<?php

namespace Mrss\Service;

use Mrss\Entity\BenchmarkGroup;
use Mrss\Entity\Exception\InvalidBenchmarkException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Debug\Debug;
use Mrss\Entity\College;
use Mrss\Entity\Observation;
use Mrss\Entity\Benchmark;
use Mrss\Model;
use Zend\Db\Sql\Sql;

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
     * @var
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
     * @var array
     */
    protected $stats = array('imported' => 0, 'skipped' => 0);

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

        foreach ($result as $row) {
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

        $this->entityManager->flush();
    }

    /**
     * Import Observations
     */
    public function importObservations()
    {
        // This may take some time
        set_time_limit(600);

        $query = "select n.title, y.field_data_entry_year_value as year, sss.*
from content_type_group_form18_stud_serv_staff sss
inner join node n on n.nid = sss.nid
inner join content_field_data_entry_year y on y.nid = n.nid
where field_18_stud_act_staff_ratio_value is not null";

        $statement = $this->dbAdapter->query($query);
        $result = $statement->execute();

        $i = 0;
        foreach ($result as $row) {
            /*if ($i++ > 30) {
                break;
            }*/

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
                continue;
            }

            // Now we have a new or existing observation and we can populate it
            $observation->setYear($year);
            $observation->setCollege($college);
            $observation->setCipCode(0);


            // Loop over the row's columns, convert the field name and set the value
            foreach ($row as $key => $value) {
                // If the fieldname isn't converted, just skip it
                try {
                    $fieldName = $this->convertFieldName($key);
                    $observation->set($fieldName, $value);
                } catch (\Exception $e) {
                    continue;
                }
            }

            $this->getObservationModel()->save($observation);

            $this->stats['imported']++;

            // Write to the db every 20 rows
            if ($i++ % 20 == 0) {
                $this->entityManager->flush();
            }
        }

        // Save the data to the db
        $this->entityManager->flush();

        $stats = $this->getStats();
        //Debug::dump($stats); die('done.');
    }

    public function importFieldMetadata()
    {

        $sql = new Sql($this->dbAdapter);
        $select = $sql->select();

        $select->from('content_node_field_instance');
        $select->columns(
            array(
                'field_name',
                'label',
                'description',
                'weight',
                'widget_type'
            )
        );

        $statement = $sql->prepareStatementForSqlObject($select);

        $results = $statement->execute();

        // Observation entity for seeing what fields we have
        $exampleObservation = new Observation();

        foreach ($results as $result) {
            try {
                $dbColumn = $this->convertFieldName(
                    $result['field_name'],
                    false
                );
            } catch (\Exception $e) {
                continue;
            }


            if ($exampleObservation->has($dbColumn)) {
                Debug::dump($result);
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

                $this->getBenchmarkModel()->save($benchmark);
                $this->stats['imported']++;
            } else {
                $this->stats['skipped']++;
            }
        }

        // Save the data to the db
        $this->entityManager->flush();
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
        $pattern = '/^field_(.\d)_(.*)_value$/';
        if (!$includeValue) {
            $pattern = '/^field_(.\d)_(.*)$/';
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

    public function getStats()
    {
        return $this->stats;
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

    protected function getObservationModel()
    {
        return $this->observationModel;
    }

    public function setBenchmarkModel($model)
    {
        $this->benchmarkModel = $model;

        return $this;
    }

    protected function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }
}
