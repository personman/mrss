<?php

namespace Mrss\Service;

use Mrss\Entity\Exception\InvalidBenchmarkException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\Debug\Debug;
use Mrss\Entity\College;
use Mrss\Entity\User;
use Mrss\Entity\Observation;
use Mrss\Entity\Benchmark;
use Mrss\Entity\BenchmarkGroup;
use Mrss\Entity\Study;
use Mrss\Entity\Subscription;
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
     * @var \Mrss\Model\user
     */
    protected $userModel;

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

    /**
     * @var \Mrss\Model\Study
     */
    protected $studyModel;

    /**
     * @var \Mrss\Model\Subscription
     */
    protected $subscriptionModel;

    /**
     * @var \Mrss\Model\Setting
     */
    protected $settingModel;

    /**
     * @var \Mrss\Service\ObservationAudit
     */
    protected $observationAudit;

    protected $importProgressPrefix = 'nccbp-import-';

    protected $type;

    protected $colleges = array();

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
        $this->setType('colleges');

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

            if ($i % 20 == 0) {
                $this->saveProgress($i - 1);
            }

            $ipeds = $this->padIpeds($row['field_ipeds_id_value']);

            // Does this college already exist?
            $existingCollege = $this->getCollegeModel()->findOneByIpeds($ipeds);

            // This class will need to know very little about
            // Doctrine ORM. We do still have the flush() call below.

            if (!empty($existingCollege)) {
                // Skip this college as we've already imported it
                //$this->stats['skipped']++;

                //continue;
                $college = $existingCollege;
            } else {
                $college = new College;
            }

            // College name
            $college->setName($row['field_institution_name_value']);

            // Ipeds
            $college->setIpeds($ipeds);

            // Address
            $college->setAddress($row['field_address_value']);
            $college->setCity($row['field_city_value']);
            $college->setState($row['field_state_value']);
            $college->setZip($row['field_zip_code_value']);

            $college->setExecTitle($row['field_president_title_value']);
            $college->setExecSalutation($row['field_president_salutation_value']);
            $college->setExecFirstName($row['field_president_first_value']);
            $college->setExecMiddleName($row['field_president_middle_value']);
            $college->setExecLastName($row['field_president_last_value']);

            $this->getCollegeModel()->save($college);

            $this->stats['imported']++;
        }

        $this->saveProgress($count, $count);

        $this->entityManager->flush();
    }

    public function importUsers()
    {
        $this->setType('users');

        // Exclude Michelle, Chad and Jeff
        $query = "select n.title AS college, u.mail, u.uid, u.pass,
            sal.value AS salutation, name.value as name, title.value as title,
            phone.value as phone, ext.value as extension,
            contact.value as contact_type
            from users u
            inner join og_uid ou ON u.uid = ou.uid
            inner join node n ON ou.nid = n.nid
            inner join profile_values sal on u.uid = sal.uid AND sal.fid = 7
            inner join profile_values name on u.uid = name.uid AND name.fid = 8
            inner join profile_values title on u.uid = title.uid AND title.fid = 9
            inner join profile_values phone on u.uid = phone.uid AND phone.fid = 10
            inner join profile_values ext on u.uid = ext.uid AND ext.fid = 11
            inner join profile_values contact on u.uid = contact.uid AND contact.fid = 51
            where u.uid not in (1, 1650, 2481)";

        $statement = $this->dbAdapter->query($query);
        $result = $statement->execute();

        $count = count($result);
        $this->saveProgress(0, $count);

        $i = 0;
        foreach ($result as $row) {
            if ($i % 20 == 0) {
                $this->saveProgress($i);
            }

            list($firstName, $lastName) = $this->parseName($row['name']);

            // If there's no college, skip them
            $college = $this->findCollegeByAppendedIpeds($row['college']);
            if (!empty($college)) {

                // Does the user already exist?
                $user = $this->getUserModel()->findOneByEmail($row['mail']);
                if (empty($user)) {
                    $user = new User;
                }

                if (empty($row['pass'])) {
                    $row['pass'] = 'fake_pass';
                }

                $user->setPrefix($row['salutation']);
                $user->setFirstName($firstName);
                $user->setLastName($lastName);
                $user->setTitle($row['title']);
                $user->setPhone($row['phone']);
                $user->setExtension($row['extension']);
                $user->setEmail($row['mail']);
                $user->setCollege($college);
                $user->setRole($this->getRole($row['contact_type']));
                $user->setPassword($row['pass']);
                $user->addStudy($this->getStudy());

                $this->getUserModel()->save($user);
            }

            $i++;
        }

        $this->saveProgress($i);

    }

    public function getRole($contact_type)
    {
        $map = array(
            'Data' => 'data',
            'Administrative' => 'contact',
            'Administrative and Data' => 'contact',
            'College System' => 'system_admin',
            'State System' => 'system_admin'
        );

        if (!empty($map[$contact_type])) {
            $role = $map[$contact_type];
        } else {
            $role = $contact_type;
        }

        return $role;
    }

    public function findCollegeByAppendedIpeds($name)
    {
        $parts = explode('_', $name);
        $ipeds = array_pop($parts);

        return $this->getCollegeByIpeds($ipeds);
    }

    public function parseName($name)
    {
        $parts = explode(' ', $name);
        $first = array_shift($parts);
        $last = implode(' ', $parts);

        return array($first, $last);
    }

    public function getTables()
    {
        $tables = array(
            'content_type_group_form1_subscriber_info', // 1
            'content_type_group_form2_student_compl_tsf', // 2
            'content_type_group_form3_stu_perf_transf', // 3
            'content_type_group_form4_cred_stud_enr', // 4
            'content_type_group_form5_stud_satis_eng', // 5
            'content_type_group_form6_stud_goal', // 6
            'content_type_group_form7_col_ret_succ', // 7
            'content_type_group_form8_dev_ret_succ', // 8
            'content_type_group_form9_dev_ret_succ_first_c', // 9
            'content_type_group_form10_career_comp', // 10
            'content_type_group_form11_ret_succ_core', // 11
            'content_type_group_form12_instw_cred_grad', // 12
            'content_type_group_form13a_minority', // 13a
            'content_type_group_form13b_hschool_grads', // 13b
            'content_type_group_form14a_market_pen_stud', // 14a
            'content_type_group_form14b_market_pen_com', // 14b
            'content_type_group_form15_fy_bni', // 15
            'content_type_group_form16a_av_cred_sect', // 16a
            'content_type_group_form16b_cred_co_stud_fac', // 16b
            'content_type_group_form16c_inst_fac_load', // 16c
            'content_type_group_form17a_dist_lear_sec_cred', // 17a
            'content_type_group_form17b_dist_learn_grad', // 17b
            'content_type_group_form18_stud_serv_staff', // 18
            'content_type_group_form19a_ret_dept', // 19a
            'content_type_group_form19b_griev_har', // 19b
            'content_type_group_form20a_cst_crh_fte_stud', // 20a
            'content_type_group_form20b_dev_train_per_empl' // 20b
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
    public function importObservations($table, $year = null)
    {
        $this->setType($table);

        // This may take some time (and RAM)
        set_time_limit(9600);
        ini_set('memory_limit', '700M');

        $query = "select n.title, y.field_data_entry_year_value as year, form.*
from $table form
inner join node n on n.nid = form.nid
inner join content_field_data_entry_year y on y.nid = n.nid";

        if ($year) {
            $query .= " WHERE y.field_data_entry_year_value = '$year'";
        }

        $statement = $this->dbAdapter->query($query);
        $result = $statement->execute();

        $total = count($result);
        $this->saveProgress(0, $total);

        $i = 0;
        foreach ($result as $row) {
            $i++;

            $ipeds = $this->extractIpedsFromTitle($row['title']);
            $ipeds = $this->padIpeds($ipeds);
            $year = $row['year'];

            // First we've got to look up the college
            $college = $this->getCollegeByIpeds($ipeds);

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

            $oldObservation = clone $observation;

            // Loop over the row's columns, convert the field name and set the value
            foreach ($row as $key => $value) {
                if ($value === null) {
                    continue;
                }

                // If the fieldname isn't converted, just skip it
                try {
                    $formName = str_replace('content_type_', '', $table);
                    $fieldName = $this->convertFieldName($key, $formName);
                    //echo "$fieldName: $value<br>\n";
                    $observation->set($fieldName, $value);
                } catch (\Exception $e) {
                    continue;
                }
            }

            $this->getObservationModel()->save($observation);

            $this->getObservationAudit()->logChanges(
                $oldObservation,
                $observation,
                'importNCCBP'
            );

            $this->stats['imported']++;

            // Write to the db every 20 rows
            if ($i % 200 == 0) {
                $this->saveProgress($i);
                // SaveProgress triggers a flush.
                //$this->entityManager->flush();
            }
        }

        // Save the data to the db
        $this->entityManager->flush();
        $this->saveProgress($i, $total);
    }

    /**
     * If we need all the colleges anyway, let's look them up at once
     *
     * @param $ipeds
     * @return null
     */
    protected function getCollegeByIpeds($ipeds)
    {
        if (empty($this->colleges)) {
            $colleges = $this->getCollegeModel()->findAll();

            foreach ($colleges as $college) {
                $this->colleges[$college->getIpeds()] = $college;
            }
        }

        if (!empty($this->colleges[$ipeds])) {
            $college = $this->colleges[$ipeds];
        } else {
            $college = null;
        }

        return $college;
    }

    /**
     * Import meta data about benchmarks, like label, description, data type,
     * form id, etc.
     */
    public function importFieldMetadata()
    {
        $this->setType('benchmarks');

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
                    $result['type_name'],
                    false
                );
            } catch (\Exception $e) {
                //echo $e->getMessage() . "\n";
                continue;
            }

            /*if (stristr($result['type_name'], '17b')) {
                pr($dbColumn);
                prd($result);
            }*/
            if ($exampleObservation->has($dbColumn)) {

                // Find the benchmarkGroup
                $benchmarkGroupShortName = str_replace(
                    'group_',
                    '',
                    $result['type_name']
                );

                $benchmarkGroup = $this->getBenchmarkGroupModel()
                    ->findOneByShortName($benchmarkGroupShortName);

                if (empty($benchmarkGroup)) {
                    echo 'benchmarkGroupShortName: ';
                    pr($benchmarkGroupShortName);
                }

                // Find or create the Benchmark entity
                $benchmark = $this->getBenchmarkModel()
                    ->findOneByDbColumnAndGroup($dbColumn, $benchmarkGroup);

                // Create the benchmark
                if (empty($benchmark)) {
                    $benchmark = new Benchmark;

                    // Now check to see if there are possible duplicates
                    $existingBenchmark = $this->getBenchmarkModel()
                        ->findOneByDbColumn($dbColumn);

                    if (!empty($existingBenchmark)) {
                        // Since a benchmark with this dbColumn (but not this
                        // benchmark group) already exists, modify the dbColumn
                        //$dbColumn = $benchmarkGroupShortName . '_' . $dbColumn;
                    }
                }

                $reportField = $this->getReportField($result['field_name']);

                // Populate the benchmark
                $benchmark->setDbColumn($dbColumn);
                $benchmark->setName($result['label']);
                $benchmark->setDescription($result['description']);
                $benchmark->setSequence($result['weight']);

                $inputType = $this->convertInputType($result['widget_type']);
                if ($inputType == 'computed') {
                    if (stristr($benchmark->getName(), 'Rate')) {
                        $inputType = 'percent';
                    } elseif (stristr($benchmark->getName(), '%')) {
                        $inputType = 'percent';
                    } elseif (stristr($benchmark->getName(), 'Percent')) {
                        $inputType = 'percent';
                    } elseif (stristr($benchmark->getName(), 'Hours')) {
                        $inputType = 'float';
                    }
                    $benchmark->setComputed(true);
                }

                //if ($inputType != 'computed') {
                    $benchmark->setInputType($inputType);
                //}

                $benchmark->setYearsAvailable(
                    $this->getYearsAvailable($result['field_name'])
                );
                $benchmark->setExcludeFromCompletion(false);


                $benchmark->setHighIsBetter(true);

                if (empty($reportField)) {
                    $benchmark->setIncludeInNationalReport(false);
                    $benchmark->setIncludeInBestPerformer(false);
                } else {
                    $benchmark->setIncludeInNationalReport(true);
                    $benchmark->setReportLabel(
                        $reportField['field_rf_benchmark_report_label_value']
                    );
                    $benchmark->setYearPrefix($reportField['field_rf_prefix_value']);
                    $benchmark->setYearOffset(
                        $reportField['field_rf_data_collection_year_value']
                    );
                    $benchmark->setIncludeInBestPerformer(
                        ($reportField['field_rf_best_practices_value'] > 0)
                    );
                    $benchmark->setReportWeight(
                        $reportField['field_rf_weight_value']
                    );
                    $benchmark->setPeerReportLabel(
                        $reportField['field_rf_peer_report_label_value']
                    );
                    $benchmark->setDescriptiveReportLabel(
                        $reportField['field_descriptive_label_value']
                    );

                    $hib = $reportField['field_rf_best_practices_high_low_value'];
                    $hib = (intval($hib) == 1);
                    $benchmark->setHighIsBetter($hib);


                }

                // Set the benchmark group
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
        // Make sure the study is there
        $study = $this->getStudy();

        $this->setType('benchmarkGroups');

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
            $benchmarkGroup->setStudy($study);

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

    public function importSubscriptions($year = null)
    {
        // This may take some time (and RAM)
        set_time_limit(9600);
        ini_set('memory_limit', '512M');

        $this->setType('subscriptions');

        $yearWhere = '';
        if ($year) {
            $yearWhere = " AND field_years_value = '$year' ";
        }

        $query = "SELECT field_institution_name_value, field_ipeds_id_value, field_years_value, payment_amount
            FROM content_type_group_subs_info
            LEFT JOIN content_field_years
            ON content_type_group_subs_info.nid = content_field_years.nid
            INNER JOIN nccbp_payment_institution i ON i.ipeds_id = field_ipeds_id_value
            INNER JOIN nccbp_payment_upay p ON i.session_hash = p.ext_trans_id
            WHERE field_years_value IS NOT NULL
            $yearWhere
            ORDER BY field_years_value";

        $statement = $this->dbAdapter->query($query);
        $result = $statement->execute();

        $total = count($result);
        $this->saveProgress(0, $total);

        $studyId = 1;
        $i = 0;
        foreach ($result as $row) {
            $i++;

            $ipeds = $row['field_ipeds_id_value'];
            $year = $row['field_years_value'];

            // Find the college
            $college = $this->getCollegeByIpeds($ipeds);
            if (empty($college)) {
                continue;
            }

            // Look for a subscription
            $subscription = $this->getSubscriptionModel()
                ->findOne($year, $college->getId(), $studyId);

            if (empty($subscription)) {
                // Create a new subscription
                $subscription = new Subscription;
            }

            // Look up the observation
            $observation = $this->getObservationModel()
                ->findOne($college->getId(), $year);

            $subscription->setCollege($college);
            $subscription->setStudy($this->getStudy());
            $subscription->setYear($year);
            $subscription->setStatus('imported');
            $subscription->setPaymentAmount($row['payment_amount']);

            if (!empty($observation)) {
                $subscription->setObservation($observation);
            }

            $this->getSubscriptionModel()->save($subscription);




            // Flush every so often
            if ($i % 100 == 0) {
                $this->saveProgress($i);
                // saveProgress flushes
                //$this->entityManager->flush();
            }
        }

        $this->saveProgress($i);
    }

    /**
     * Fetch or create the NCCBP study
     *
     * @return Study
     */
    public function getStudy()
    {
        $study = $this->getStudyModel()->find(1);

        /* This is handled in a migration now
        if (empty($study)) {
            $study = new Study;
            $study->setName('NCCBP');
            $study->setDescription('National Community College Benchmark Project');
            $this->getStudyModel()->save($study);
        }*/

        return $study;
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

    protected function getYearsAvailable($field)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select();

        $select->columns(
            array(
                'field_rf_years_available_value'
            )
        );
        $select->from('content_field_rf_years_available');
        $select->join(
            'node',
            'content_field_rf_years_available.nid = node.nid',
            array()
        );
        $select->where(array('title' => $field));

        $statement = $sql->prepareStatementForSqlObject($select);

        $results = $statement->execute();

        $years = array();
        foreach ($results as $row) {
            $years[] = $row['field_rf_years_available_value'];
        }

        // If there are no years, make it all years
        if (count($years) == 0) {
            $years = array(2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016);
        }

        return $years;

    }

    /**
     * Convert from nccbp field name to mrss field name
     *
     * @param $fieldName
     * @param $formName
     * @param bool $includeValue
     * @throws \Exception
     * @return string
     */
    public function convertFieldName($fieldName, $formName, $includeValue = true)
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

        // Since class properties can't begin with a number, prepend an 'n'
        if (preg_match('/^\d/', $converted)) {
            $converted = 'n' . $converted;
        }

        // Remove 'group_' from form name
        $formName = str_replace('group_', '', $formName);

        // Now see if we need to prepend the form name
        $emptyObservation = new Observation();
        $nameWithForm = $formName . '_' . $converted;
        if ($emptyObservation->has($nameWithForm)) {
            $converted = $nameWithForm;
        }

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
     * @param $type
     * @return array|mixed
     */
    public function getProgress($type)
    {
        //$filename = $this->progressFile . '-' . $type;

        //$session = new \Zend\Session\Container('nccbp');
        //$stats = Json::decode($session->$type);

        $stats = $this->getSettingModel()->getValueForIdentifier(
            $this->importProgressPrefix . $type
        );


        if (!empty($stats)) {
            $stats = Json::decode($stats, \Zend\Json\Json::TYPE_ARRAY);

            // If it's complete, clear the progress
            if (isset($stats['processed']) && isset($stats['total']) &&
                $stats['processed'] == $stats['total']) {

                $this->getSettingModel()->setValueForIdentifier(
                    $this->importProgressPrefix . $type,
                    ''
                );
            }
        } else {
            $stats = array('status' => 'no import running');
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
        //$filename = $this->progressFile . '-' . $this->getType();
        //$result = file_put_contents($filename, Json::encode($this->stats));

        // Write to a session
        //$session = new \Zend\Session\Container('nccbp');
        //$type = $this->getType();
        //$session->$type = Json::encode($this->stats);
        //var_dump(Json::encode($this->stats)); die('saveProgress');

        // Write it to the db
        $this->getSettingModel()->setValueForIdentifier(
            $this->importProgressPrefix . $this->getType(),
            Json::encode($this->stats)
        );
        //prd($this->importProgressPrefix . $this->getType());

        // Write to the console
        echo round($percentage, 1) . "%\n";
    }

    /**
     * Return some import meta data
     *
     * @return array
     */
    public function getImports()
    {
        $imports = array(
            'colleges' => array(
                'label' => 'Colleges',
                'method' => 'importColleges'
            ),
            'users' => array(
                'label' => 'Users',
                'method' => 'importUsers'
            ),
            'benchmarkGroups' => array(
                'label' => 'Benchmark Groups',
                'method' => 'importBenchmarkGroups'
            ),
            'benchmarks' => array(
                'label' => 'Benchmarks',
                'method' => 'importFieldMetadata'
            ),
            /*'observations' => array(
                'label' => 'Observations',
                'method' => 'importAllObservations'
            )*/
        );

        // Add the observation tables
        foreach ($this->getTables() as $table) {
            preg_match('/(form([0-9]+)([a-z]?)_)/', $table, $matches);
            $formNumber = $matches[2];
            if (!empty($matches[3])) {
                $formNumber .= ' ' . strtoupper($matches[3]);
            }

            $imports[$table] = array(
                'label' => 'Form ' . $formNumber,
                'method' => 'importObservations',
                'argument' => $table,
                'year' => true
            );
        }

        // Add subscription importer at the end
        $imports['subscriptions'] = array(
            'label' => 'Subscriptions',
            'method' => 'importSubscriptions',
            'year' => true
        );

        return $imports;
    }

    protected function getReportField($key)
    {
        $sql = new Sql($this->dbAdapter);
        $select = $sql->select();

        $select->columns(
            array(
                'field_rf_benchmark_report_label_value',
                'field_rf_prefix_value',
                'field_rf_data_collection_year_value',
                'field_rf_best_practices_value',
                'field_rf_weight_value',
                'field_rf_peer_report_label_value',
                'field_rf_best_practices_high_low_value',
                'field_descriptive_label_value'
            )
        );
        $select->from('content_type_report_fields');
        $select->join(
            'node',
            'content_type_report_fields.nid = node.nid',
            array('title')
        );
        $select->where(array('title' => $key));

        $statement = $sql->prepareStatementForSqlObject($select);

        $results = $statement->execute();

        $reportField = null;
        if ($results) {
            $reportField = $results->next();
        }

        return $reportField;

    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
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

    public function setUserModel($model)
    {
        $this->userModel = $model;

        return $this;
    }

    public function getUserModel()
    {
        return $this->userModel;
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

    public function setStudyModel($model)
    {
        $this->studyModel = $model;

        return $this;
    }

    public function getStudyModel()
    {
        return $this->studyModel;
    }

    public function setSubscriptionModel($model)
    {
        $this->subscriptionModel = $model;

        return $this;
    }

    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
    }

    public function setSettingModel($model)
    {
        $this->settingModel = $model;

        return $this;
    }

    public function getSettingModel()
    {
        return $this->settingModel;
    }

    public function setObservationAudit($audit)
    {
        $this->observationAudit = $audit;

        return $this;
    }

    public function getObservationAudit()
    {
        return $this->observationAudit;
    }
}
