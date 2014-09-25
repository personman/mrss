<?php

namespace Mrss\Service;

use Mrss\Entity\Study;
use Mrss\Model\Benchmark as BenchmarkModel;
use Mrss\Model\BenchmarkGroup as BenchmarkGroupModel;
use Mrss\Entity\BenchmarkGroup;
use Mrss\Entity\Benchmark;
use Mrss\Entity\BenchmarkHeading;
use Mrss\Entity\Observation;

class ImportBenchmarks
{
    /** @var Study */
    protected $study;

    /** @var BenchmarkModel */
    protected $benchmarkModel;

    /** @var BenchmarkGroupModel */
    protected $benchmarkGroupModel;

    protected $entityManager;

    /** @var Observation */
    protected $observation;

    /** @var ComputedFields */
    protected $computedFieldsService;

    protected $messages = array();

    protected $sequences = array();

    protected $headings = array();

    /**
     * Doesn't currently modify benchmark sequence
     *
     * @param $filename
     * @throws \Exception
     */
    public function import($filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception("Import file $filename does not exist.");
        }

        ini_set('auto_detect_line_endings', true);

        $fh = fopen($filename, 'r');
        $headers = array();
        while (($data = fgetcsv($fh)) !== false) {
            if (count($data) < 2) {
                continue;
            }

            if (count($headers) == 0) {
                $headers = $data;
                $this->checkHeaders($headers);
            } else {
                $row = array();
                foreach ($headers as $key => $field) {

                    if (isset($data[$key])) {
                        $row[$field] = trim($data[$key]);
                    } else {
                        $row[$field] = false;
                    }
                }

                // Check equation
                $this->checkEquation($row['equation'], $row['dbColumn']);

                // Import row
                $this->importRow($row);
            }
        }

        $this->saveHeadings();
    }

    public function checkEquation($equation, $dbColumn)
    {
        if (!empty($equation)) {
            $result = $this->getComputedFieldsService()->checkEquation($equation);

            if (!$result) {
                $error = $this->getComputedFieldsService()->getError();
                $error = "<br><br>Error in equation for $dbColumn: $error<br>";
                $this->messages['error'][] = $error;
            }
        }
    }

    public function getMessages()
    {
        $allMessages = '';
        foreach ($this->messages as $type => $messages) {
            $allMessages .= implode('', $messages);
        }

        return $allMessages;
    }

    public function importRow($row)
    {
        // Find or create the benchmarkGroup
        $benchmarkGroup = $this->findOrCreateBenchmarkGroup($row);

        // Is it a benchmark or heading?
        if ($row['inputType'] == 'heading') {
            $this->addHeading($row, $benchmarkGroup);
        } else {
            // Update or create the benchmark
            $benchmark = $this->updateOrCreateBenchmark($row, $benchmarkGroup);

            // See if it exists in the observation entity
            $this->checkObservation($benchmark);

        }

        $this->entityManager->flush();
    }

    public function addHeading($row, $benchmarkGroup)
    {
        $gId = $benchmarkGroup->getId();
        if (!isset($this->headings[$gId])) {
            $this->headings[$gId] = array();
        }

        $heading = new BenchmarkHeading();
        $heading->setBenchmarkGroup($benchmarkGroup);
        $heading->setName($row['name']);
        $heading->setDescription($row['description']);
        $heading->setDbColumn($row['dbColumn']);
        $heading->setSequence($this->getSequence($heading));

        $this->headings[$gId][] = $heading;
    }

    public function saveHeadings()
    {
        foreach ($this->headings as $gId => $headings) {
            $this->getBenchmarkGroupModel()->find($gId)
                ->setBenchmarkHeadings($headings);
        }

        $this->entityManager->flush();
    }

    public function findOrCreateBenchmarkGroup($row)
    {
        $benchmarkGroup = $this->getBenchmarkGroupModel()
            ->findOneByName($row['benchmarkGroup']);

        if (empty($benchmarkGroup)) {
            $benchmarkGroup = new BenchmarkGroup;

            $benchmarkGroup->setName($row['benchmarkGroup']);
            $benchmarkGroup->setStudy($this->study);
            $benchmarkGroup->setShortName($row['benchmarkGroup']);
            $benchmarkGroup->setDescription('');

            // Save it
            $this->getBenchmarkGroupModel()->save($benchmarkGroup);
        }

        return $benchmarkGroup;
    }

    public function updateOrCreateBenchmark($row, $benchmarkGroup)
    {
        $benchmark = $this->getBenchmarkModel()
            ->findOneByDbColumnAndGroup($row['dbColumn'], $benchmarkGroup);

        if (empty($benchmark)) {
            $benchmark = new Benchmark;
        }

        // Remove colon from name
        $name = str_replace(':', '', $row['name']);

        $benchmark->setBenchmarkGroup($benchmarkGroup);
        $benchmark->setName($name);
        $benchmark->setReportLabel($row['reportLabel']);
        $benchmark->setPeerReportLabel($row['peerReportLabel']);
        $benchmark->setDescriptiveReportLabel($row['descriptiveReportLabel']);
        $benchmark->setYearPrefix($row['yearPrefix']);
        $benchmark->setYearOffset($row['yearOffset']);
        $benchmark->setIncludeInBestPerformer($row['includeInBestPerformer']);
        $benchmark->setHighIsBetter($row['highIsBetter']);
        $benchmark->setDbColumn($row['dbColumn']);
        $benchmark->setInputType($row['inputType']);
        $benchmark->setDescription($row['description']);
        $benchmark->setOptions($row['options']);
        $benchmark->setComputed($row['computed']);
        $benchmark->setEquation(($row['equation']));
        $benchmark->setExcludeFromCompletion(($row['excludeFromCompletion']));
        $benchmark->setIncludeInNationalReport(($row['includeInNationalReport']));
        $benchmark->setYearsAvailable($this->getYears());
        $benchmark->setSequence($this->getSequence($benchmark));
        $yearsAvailable = explode(',', $row['yearsAvailable']);
        $benchmark->setYearsAvailable($yearsAvailable);

        $exclude = $benchmark->getExcludeFromCompletion();
        $benchmark->setExcludeFromCompletion($exclude);

        // Save it
        $this->getBenchmarkModel()->save($benchmark);

        return $benchmark;
    }

    /**
     * Get the sequence by keeping track of the number of benchmarks per group
     *
     * @param $benchmark
     */
    public function getSequence($benchmark)
    {
        $benchmarkGroupName = $benchmark->getBenchmarkGroup()->getName();
        if (!isset($this->sequences[$benchmarkGroupName])) {
            $this->sequences[$benchmarkGroupName] = 1;
        }

        $sequence = $this->sequences[$benchmarkGroupName];
        $this->sequences[$benchmarkGroupName]++;

        return $sequence;
    }

    /**
     * Look for benchmarks that haven't been added to the Observation class yet
     *
     * @param Benchmark $benchmark
     */
    public function checkObservation(Benchmark $benchmark)
    {
        $observation = $this->getObservation();
        $dbColumn = $benchmark->getDbColumn();

        if (!$observation->has($dbColumn)) {
            $type = $this->getTypeByInputType($benchmark->getInputType());
            $code = "\n/** @ORM\Column(type=\"$type\", nullable=true) */".
                "\nprotected $$dbColumn;\n";
            $this->messages['property'][] = $code;
        }
    }

    public function getTypeByInputType($inputType)
    {
        $map = array(
            'number' => 'integer',
            'dollars' => 'float',
            'wholedollars' => 'float',
            'percentage' => 'float',
            'percent' => 'float',
            'wholePercent' => 'integer',
            'computed' => 'float',
            'text' => 'string'
        );

        if (!empty($map[$inputType])) {
            $type = $map[$inputType];
        } else {
            // Default to string
            $type = 'string';
        }

        return $type;
    }

    public function getYears()
    {
        $year = date('Y');
        $year2 = $year + 1;
        $year3 = $year + 2;
        $year0 = $year - 1;

        return array($year0, $year, $year2, $year3);
    }

    public function export($study, $filename)
    {
        // Place header
        $headers = $this->getHeaders();

        $rows = $this->getBenchmarkInfo($study);

        // Save to file
        if (!file_exists($filename)) {
            throw new \Exception("Export file $filename does not exist.");
        }

        $fh = fopen($filename, 'w');

        fputcsv($fh, $headers);
        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }
        fclose($fh);

        return true;
    }

    public function getBenchmarkInfo(Study $study)
    {
        // Loop over benchmark Groups
        $studyBenchmarks = array();
        foreach ($study->getBenchmarkGroups() as $benchmarkGroup) {
            $formName = $benchmarkGroup->getName();
            $children = $benchmarkGroup->getChildren();

            foreach ($children as $child) {
                if ($child instanceof BenchmarkHeading) {
                    $heading = $child;
                    $studyBenchmarks[] = array(
                        $formName,
                        $heading->getName(),
                        null,
                        null,
                        null,
                        null,
                        null,
                        $heading->getDbColumn(),
                        'heading',
                        $heading->getDescription()
                    );

                } else {
                    /** @var Benchmark $benchmark */
                    $benchmark = $child;

                    $studyBenchmarks[] = array(
                        $formName,
                        $benchmark->getName(),
                        $benchmark->getReportLabel(),
                        $benchmark->getPeerReportLabel(),
                        $benchmark->getDescriptiveReportLabel(),
                        $benchmark->getYearPrefix(),
                        $benchmark->getYearOffset(),
                        $benchmark->getDbColumn(),
                        $benchmark->getInputType(),
                        $benchmark->getDescription(),
                        $benchmark->getOptions(),
                        $benchmark->getComputed(),
                        $benchmark->getEquation(),
                        $benchmark->getExcludeFromCompletion(),
                        $benchmark->getIncludeInNationalReport(),
                        $benchmark->getIncludeInBestPerformer(),
                        $benchmark->getHighIsBetter(),
                        implode(',', $benchmark->getYearsAvailable())
                    );

                }

            }
        }

        return $studyBenchmarks;
    }

    public function setStudy(Study $study)
    {
        $this->study = $study;
    }

    public function setBenchmarkModel(BenchmarkModel $benchmarkModel)
    {
        $this->benchmarkModel = $benchmarkModel;

        return $this;
    }

    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }

    public function setBenchmarkGroupModel(BenchmarkGroupModel $model)
    {
        $this->benchmarkGroupModel = $model;

        return $this;
    }

    public function getBenchmarkGroupModel()
    {
        return $this->benchmarkGroupModel;
    }

    public function setEntityManager($em)
    {
        $this->entityManager = $em;
    }

    /**
     * A sample (empty) observation
     */
    public function getObservation()
    {
        if (empty($this->observation)) {
            $this->observation = new Observation();
        }

        return $this->observation;
    }

    protected function checkHeaders($headers)
    {
        $expected = $this->getHeaders();

        $diff = array_diff($expected, $headers);

        if (count($diff)) {
            $missing = implode(', ', $diff);
            throw new \Exception("CSV invalid. Headers are missing: $missing");
        }
    }

    protected function getHeaders()
    {
        return array(
            'benchmarkGroup',
            'name',
            'reportLabel',
            'peerReportLabel',
            'descriptiveReportLabel',
            'yearPrefix',
            'yearOffset',
            'dbColumn',
            'inputType',
            'description',
            'options',
            'computed',
            'equation',
            'excludeFromCompletion',
            'includeInNationalReport',
            'includeInBestPerformer',
            'highIsBetter',
            'yearsAvailable'
        );
    }

    public function setComputedFieldsService(ComputedFields $service)
    {
        $this->computedFieldsService = $service;

        return $this;
    }

    public function getComputedFieldsService()
    {
        return $this->computedFieldsService;
    }
}
