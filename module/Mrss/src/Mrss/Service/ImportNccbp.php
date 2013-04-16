<?php

namespace Mrss\Service;

use Mrss\Entity\Exception\InvalidBenchmarkException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Debug\Debug;
use Mrss\Entity\College;
use Mrss\Entity\Observation;
use Mrss\Entity\Benchmark;
use Mrss\Entity\BenchmarkGroup;
use Mrss\Model;
use Zend\Db\Sql\Sql;
use Zend\Session\Container;
use Zend\Json\Json;

/**
 * Import data from NCCBP database
 *
 * Get nccbp db, query, translate to entity, save
 */
class ImportNccbp
{
    /**
     * The nccbp db using zend db
     * @var
     */
    protected $dbAdapter;

    /**
     * The mrss doctrine entity manager
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Mrss\Model\College
     */
    protected $collegeModel;

    /**
     * @var \Mrss\Model\Observation
     */
    protected $observationModel;

    /**
     * @var \Mrss\Model\Benchmark
     */
    protected $benchmarkModel;

    /**
     * @var \Mrss\Model\BenchmarkGroup
     */
    protected $benchmarkGroupModel;

    protected $progressFile = "/tmp/nccbp-import-progress";

    /**
     * @var array
     */
    protected $stats = array(
        'imported' => 0,
        'skipped' => 0,
        'start' => '',
        'elapsed' => '',
        'processed' => 0,
        'total' => null,
        'percentage' => 0,
        'tableProcessed' => 0,
        'tableTotal' => null
    );

    /**
     * Constructor
     *
     * @param \Zend\Db\Adapter\Adapter $dbAdapter
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct($dbAdapter, $entityManager)
    {
        $this->dbAdapter = $dbAdapter;
        $this->entityManager = $entityManager;
        $this->stats['start'] = microtime(true);
    }

    /**
     * Import colleges from NCCBP
     *
     * Connect to the db with Zend_Db, query for colleges, check for dupliates,
     * make an entity out of each college, save
     */
    public function importColleges()
    {
        $query = "select g.title, i.*
from content_type_group_subs_info i
inner join node n on n.nid = i.nid
inner join og_ancestry a on n.nid = a.nid
inner join node g on a.group_nid = g.nid";

        $statement = $this->dbAdapter->query($query);
        $result = $statement->execute();

        $count = count($result);
        //die("Count about to be set: $count");
        $this->saveProgress(0, $count);

        $i = 0;
        foreach ($result as $row) {
            $i++;

            $this->saveProgress($i - 1);

            $ipeds = $this->padIpeds($row['field_ipeds_id_value']);

            // Does this college already exist?
            $existingCollege = $this->getCollegeModel()->findOneByIpeds($ipeds);

            // This class will need to know very little about
            // Doctrine ORM. We do still have the flush() call below.

            if (!empty($existingCollege)) {
                // Skip this college as we've already imported it
                $this->stats['skipped']++;

                continue;
            }

            // Populate the college
            $college = new College;

            // College name
            $college->setName($row['field_institution_name_value']);

            // Ipeds
            $college->setIpeds($ipeds);

            // Address
            $college->setAddress($row['field_address_value']);
            $college->setCity($row['field_city_value']);
            $college->setState($row['field_state_value']);
            $college->setZip($row['field_zip_code_value']);

            $this->getCollegeModel()->save($college);

            $this->stats['imported']++;
        }

        $this->saveProgress($count, $count);

        $this->entityManager->flush();
    }

    public function getTables()
    {
        $tables = array(
            //'content_type_group_form1_subscriber_info', // 1
            //'content_type_group_form2_student_compl_tsf', // 2
            //'content_type_group_form3_stu_perf_transf', // 3
            //'content_type_group_form4_cred_stud_enr', // 4
            //'content_type_group_form5_stud_satis_eng', // 5
            //'content_type_group_form6_stud_goal', // 6
            'content_type_group_form7_col_ret_succ', // 7
            //'content_type_group_form8_dev_ret_succ', // 8
            //'content_type_group_form9_dev_ret_succ_first_c', // 9
            //'content_type_group_form10_career_comp', // 10
            //'content_type_group_form18_stud_serv_staff' // 18
        );

        return $tables;
    }

    public function importAllObservations()
    {
        $tables = $this->getTables();
        $this->stats['tableTotal'] = count($tables);

        $i = 1;
        foreach ($tables as $table) {
            $this->stats['currentTable'] = $table;

            $this->importObservations($table);

            $this->setTableProcessed($i);

            $i++;
        }
    }

    /**
     * Import Observations
     */
    public function importObservations($table)
    {
        // This may take some time
        set_time_limit(600);

        $query = "select n.title, y.field_data_entry_year_value as year, form.*
from $table form
inner join node n on n.nid = form.nid
inner join content_field_data_entry_year y on y.nid = n.nid";

        $statement = $this->dbAdapter->query($query);
        $result = $statement->execute();

        $total = count($result);
        $this->saveProgress(0, $total);

        $i = 0;
        foreach ($result as $row) {
            $i++;
            $this->saveProgress($i);

            $ipeds = $this->extractIpedsFromTitle($row['title']);
            $ipeds = $this->padIpeds($ipeds);
            $year = $row['year'];

            // First we've got to look up the college
            $college = $this->getCollegeModel()->findOneByIpeds($ipeds);

            if (empty($college)) {
                $this->stats['skipped']++;

                continue;
            }

            // Now see if an observation exists already
            $observation = $this->getObservationModel()->findOne(
                $college->getId(),
                $year
            );

            // If not, create one
            if (empty($observation)) {
                $observation = new Observation();
            } else {
                // Don't update existing records:
                //continue;
            }

            // Now we have a new or existing observation and we can populate it
            $observation->setYear($year);
            $observation->setCollege($college);
            $observation->setCipCode(0);


            // Loop over the row's columns, convert the field name and set the value
            foreach ($row as $key => $value) {
                if ($value === null) {
                    continue;
                }

                // If the fieldname isn't converted, just skip it
                try {
                    $fieldName = $this->convertFieldName($key);
                    //echo "$fieldName: $value<br>\n";
                    $observation->set($fieldName, $value);
                } catch (\Exception $e) {
                    continue;
                }
            }

            $this->getObservationModel()->save($observation);

            $this->stats['imported']++;

            // Write to the db every 20 rows
            if ($i % 20 == 0) {
                $this->entityManager->flush();
            }
        }

        // Save the data to the db
        $this->entityManager->flush();
        $this->saveProgress($i, $total);
    }

    /**
     * Import meta data about benchmarks, like label, description, data type,
     * form id, etc.
     */
    public function importFieldMetadata()
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select();

        $select->from('content_node_field_instance');
        $select->columns(
            array(
                'field_name',
                'type_name', // The foreign key for the benchmarkGroup
                'label',
                'description',
                'weight',
                'widget_type'
            )
        );

        $statement = $sql->prepareStatementForSqlObject($select);

        $results = $statement->execute();

        $this->saveProgress(0, count($results));

        // Observation entity for seeing what fields we have
        $exampleObservation = new Observation();

        $i = 0;
        foreach ($results as $result) {
            $i++;

            try {
                $dbColumn = $this->convertFieldName(
                    $result['field_name'],
                    false
                );
            } catch (\Exception $e) {
                continue;
            }

            if ($exampleObservation->has($dbColumn)) {
                // Find or create the Benchmark entity
                $benchmark = $this->getBenchmarkModel()
                    ->findOneByDbColumn($dbColumn);
                
                if (empty($benchmark)) {
                    $benchmark = new Benchmark;
                }

                // Populate the benchmark
                $benchmark->setDbColumn($dbColumn);
                $benchmark->setName($result['label']);
                $benchmark->setDescription($result['description']);
                $benchmark->setSequence($result['weight']);
                $inputType = $this->convertInputType($result['widget_type']);
                $benchmark->setInputType($inputType);

                // Find and set the benchmarkGroup
                $benchmarkGroup = $this->getBenchmarkGroupModel()
                    ->findOneByShortName($result['type_name']);
                if (!empty($benchmarkGroup)) {
                    $benchmark->setBenchmarkGroup($benchmarkGroup);
                }

                $this->getBenchmarkModel()->save($benchmark);
                $this->stats['imported']++;
            } else {
                $this->stats['skipped']++;
            }

            $this->saveProgress($i);
        }

        // Save the data to the db
        $this->entityManager->flush();

        $this->saveProgress($i);
    }

    /**
     * Import benchmark groups (forms) from nccbp
     */
    public function importBenchmarkGroups()
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select();

        $select->from('node_type');
        $select->columns(
            array(
                'name',
                'type',
                'description',
                'help'
            )
        );

        $statement = $sql->prepareStatementForSqlObject($select);

        $results = $statement->execute();

        $this->saveProgress(0, count($results));

        $i = 0;
        foreach ($results as $result) {
            $i++;

            // Skip content types that aren't benchmark forms
            if (!strstr($result['name'], 'Form')) {
                continue;
            }

            // Find or create the benchmarkGroup
            $benchmarkGroup = $this->getBenchmarkGroupModel()
                ->findOneByShortName($result['type']);

            if (empty($benchmarkGroup)) {
                $benchmarkGroup = new BenchmarkGroup();
            }

            // Populate the BenchmarkGroup
            $benchmarkGroup->setName($result['name']);
            $benchmarkGroup->setShortName($result['type']);

            // Merge the description and help fields
            $description = $result['description'] . '. ' . $result['help'];
            $benchmarkGroup->setDescription($description);

            $this->getBenchmarkGroupModel()->save($benchmarkGroup);

            $this->saveProgress($i);
        }

        // Save the data to the db
        $this->entityManager->flush();

        $this->saveProgress($i);
    }

    /**
     * Get the column names from the observations table, minus some.
     *
     * @return array
     */
    public function getObservationFields()
    {
        $schemaManager = $this->entityManager->getConnection()->getSchemaManager();

        $allColumns = $schemaManager->listTableColumns('observations');
        $exclude = array('id', 'college_id', 'year', 'cipCode');
        $columns = array();
        foreach ($allColumns as $column) {
            if (!in_array($column->getName(), $exclude)) {
                $columns[] = $column->getName();
            }
        }

        return $columns;
    }

    public function extractIpedsFromTitle($title)
    {
        $titleParts = explode('_', $title);
        $ipeds = array_pop($titleParts);

        return $ipeds;
    }

    /**
     * Convert from nccbp field name to mrss field name
     *
     * @param $fieldName
     * @param bool $includeValue
     * @throws \Exception
     * @return string
     */
    public function convertFieldName($fieldName, $includeValue = true)
    {
        // This takes the format 'field_18_tot_fte_fin_aid_staff_value'
        // and converts it to this: 'tot_fte_fin_aid_staff'
        $pattern = '/^field_(\d+)[a-z]?_(.*)_value$/';
        if (!$includeValue) {
            $pattern = '/^field_(\d+)[a-z]?_(.*)$/';
        }

        preg_match($pattern, $fieldName, $matches);

        if (empty($matches[2])) {
            throw new \Exception("'$fieldName' is not a valid field.");
        }

        $converted = $matches[2];

        return $converted;
    }

    public function convertInputType($inputType)
    {
        $conversionMap = array(
            'text_textfield' => 'text',
            'text_textarea' => 'textarea',
            'number' => 'number',
            'optionwidgets_select' => 'select',
            'computed' => 'computed',
            'link' => 'link',
            'userreference_select' => 'user',
            'nodereference_select' => 'node',
            'imagefield_widget' => 'image',
            'optionwidgets_buttons' => 'button'
        );

        if (!empty($conversionMap[$inputType])) {
            $converted = $conversionMap[$inputType];
        } else {
            $converted = $inputType;
        }

        return $converted;
    }

    /**
     * An IPEDS is a 6-digit number. Pad with leading zeroes if needed.
     *
     * @param string $ipeds
     * @return string
     */
    public function padIpeds($ipeds)
    {
        $ipeds = str_pad($ipeds, 6, '0', STR_PAD_LEFT);

        return $ipeds;
    }

    /**
     * Get some stats about the current import
     *
     * @return array
     */
    public function getStats()
    {
        // Calculate elapsed time
        $end = microtime(true);
        $elapsed = $end - $this->stats['start'];
        $this->stats['elapsed'] = round($elapsed, 3);

        return $this->stats;
    }

    /**
     * Retrieve progress json from file
     *
     * @return array
     */
    public function getProgress()
    {
        if (file_exists($this->progressFile)) {
            $statsJson = file_get_contents($this->progressFile);
            $stats = Json::decode($statsJson, Json::TYPE_ARRAY);

            // If it's complete, delete the file
            if (isset($stats['processed']) && isset($stats['total']) &&
                $stats['processed'] == $stats['total']) {
                unlink($this->progressFile);
            }
        } else {
            $stats = array();
        }

        return $stats;
    }

    /**
     * Write progress to a little json file so we can poll for it with ajax
     *
     * @param $completed
     * @param null $total
     */
    protected function saveProgress($completed, $total = null)
    {
        $this->stats['processed'] = $completed;

        if (!is_null($total)) {
            $this->stats['total'] = $total;
        }

        // Calculate percentage
        if ($this->stats['total'] > 0) {
            $percentage = $this->stats['processed'] / $this->stats['total'] * 100;
        } else {
            $percentage = 0;
        }

        // If multiple tables are being imported, set a base percentage
        if (!is_null($this->stats['tableTotal'])) {
            // The base percentage is based on the completed tables
            $basePercentage = $this->stats['tableProcessed'] /
                $this->stats['tableTotal'] * 100;

            // Reduce the percentage so it can be added in
            $currentTablePercentage = $percentage / $this->stats['tableTotal'];

            $percentage = $basePercentage + $currentTablePercentage;
        }

        $this->stats['percentage'] = $percentage;

        // Write it to a file
        file_put_contents($this->progressFile, Json::encode($this->stats));
    }

    /**
     * Return some import meta data
     *
     * @return array
     */
    public function getImports()
    {
        return array(
            'colleges' => array(
                'label' => 'Colleges',
                'method' => 'importColleges'
            ),
            'benchmarkGroups' => array(
                'label' => 'Benchmark Groups',
                'method' => 'importBenchmarkGroups'
            ),
            'benchmarks' => array(
                'label' => 'Benchmarks',
                'method' => 'importFieldMetadata'
            ),
            'observations' => array(
                'label' => 'Observations',
                'method' => 'importAllObservations'
            )
        );
    }

    /**
     * @param integer $tablesProcessed
     */
    public function setTableProcessed($tablesProcessed)
    {
        $this->stats['tableProcessed'] = $tablesProcessed;
    }

    public function setCollegeModel($model)
    {
        $this->collegeModel = $model;

        return $this;
    }

    protected function getCollegeModel()
    {
        return $this->collegeModel;
    }

    public function setObservationModel($model)
    {
        $this->observationModel = $model;

        return $this;
    }

    public function getObservationModel()
    {
        return $this->observationModel;
    }

    public function setBenchmarkModel($model)
    {
        $this->benchmarkModel = $model;

        return $this;
    }

    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }

    public function setBenchmarkGroupModel($model)
    {
        $this->benchmarkGroupModel = $model;

        return $this;
    }

    public function getBenchmarkGroupModel()
    {
        return $this->benchmarkGroupModel;
    }
}
