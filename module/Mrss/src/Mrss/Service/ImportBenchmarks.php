<?php

namespace Mrss\Service;

use Mrss\Entity\Study;
use Mrss\Model\Benchmark as BenchmarkModel;
use Mrss\Model\BenchmarkGroup as BenchmarkGroupModel;
use Mrss\Entity\BenchmarkGroup;
use Mrss\Entity\Benchmark;
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

    protected $observationPropertiesToAdd = array();

    public function import($filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception("Import file $filename does not exist.");
        }

        ini_set('auto_detect_line_endings', true);

        $fh = fopen($filename, 'r');
        $headers = array();
        while (($data = fgetcsv($fh)) !== false) {
            if (count($headers) == 0) {
                $headers = $data;
                $this->checkHeaders($headers);
            } else {
                $row = array();
                foreach ($headers as $key => $field) {
                    $row[$field] = trim($data[$key]);
                }

                $this->importRow($row);
            }
        }
    }

    public function getObservationPropertiesToAdd()
    {
        return implode('', $this->observationPropertiesToAdd);
    }

    public function importRow($row)
    {
        // Find or create the benchmarkGroup
        $benchmarkGroup = $this->findOrCreateBenchmarkGroup($row);

        // Update or create the benchmark
        $benchmark = $this->updateOrCreateBenchmark($row, $benchmarkGroup);

        // See if it exists in the observation entity
        $this->checkObservation($benchmark);

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
        $benchmark->setDbColumn($row['dbColumn']);
        $benchmark->setInputType($row['inputType']);
        $benchmark->setDescription($row['description']);
        $benchmark->setOptions($row['options']);
        $benchmark->setEquation(($row['equation']));
        $benchmark->setYearsAvailable($this->getYears());

        // Save it
        $this->getBenchmarkModel()->save($benchmark);

        return $benchmark;
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
            $this->observationPropertiesToAdd[] = $code;
        }
    }

    public function getTypeByInputType($inputType)
    {
        $map = array(
            'number' => 'integer',
            'dollars' => 'float',
            'percentage' => 'float',
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
        $expected = array(
            'benchmarkGroup',
            'name',
            'dbColumn',
            'inputType',
            'description',
            'options',
            'equation'
        );

        $diff = array_diff($expected, $headers);

        if (count($diff)) {
            $missing = implode(', ', $diff);
            throw new \Exception("CSV invalid. Headers are missing: $missing");
        }
    }
}
