<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Benchmark metadata
 *
 * This holds info about a benchmark, like label and description,
 * but the actual data is in the observations table/entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="benchmarks")
 */
class Benchmark implements FormElementProviderInterface, InputFilterAwareInterface
{
    protected $inputFilter;
    protected $equationValidator;

    // We pass in the doctrine entity manager for validation of unique dbColumn
    protected $entityManager;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * Timeframe
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $timeframe;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $reportDescription;

    /**
     * @ORM\Column(type="string")
     */
    protected $dbColumn;

    /**
     * Default sequence is for data entry
     * @ORM\Column(type="integer")
     */
    protected $sequence;

    /**
     * @ORM\Column(type="integer")
     */
    protected $reportSequence;

    /**
     * @ORM\Column(type="string", length=30)
     */
    protected $inputType;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $required;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $computed;

    /**
     * @ORM\Column(type="integer", length=3, nullable=true)
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="BenchmarkGroup", inversedBy="benchmarks")
     */
    protected $benchmarkGroup;

    /**
     * @ORM\Column(type="string", length=60)
     */
    protected $yearsAvailable;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $options;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $equation;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $computeAfter;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $excludeFromCompletion;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $includeInNationalReport;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $reportLabel;

    /**
     * @ORM\Column(type="integer", length=3, nullable=true)
     */
    protected $reportWeight;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $peerReportLabel;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $descriptiveReportLabel;

    /**
     * @ORM\Column(type="integer", length=3, nullable=true)
     */
    protected $yearOffset;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $yearPrefix;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $includeInBestPerformer;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $highIsBetter;

    protected $benchmarkModel;
    protected $completionPercentages;

    /**
     * Construct the benchmark entity
     * Populate the years property with a placeholder
     */
    public function __construct()
    {
        $this->years = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setTimeframe($timeframe)
    {
        $this->timeframe = $timeframe;

        return $this;
    }

    public function getTimeframe()
    {
        return $this->timeframe;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setReportDescription($description)
    {
        $this->reportDescription = $description;

        return $this;
    }

    public function getReportDescription($fallback = false)
    {
        $description = $this->reportDescription;

        // If it's empty, use the data entry description
        if ($fallback && empty($description)) {
            $description = $this->getDescription();
        }

        return $description;
    }

    public function setDbColumn($column)
    {
        $this->dbColumn = $column;

        return $this;
    }

    public function getDbColumn()
    {
        return $this->dbColumn;
    }

    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getSequence()
    {
        return $this->sequence;
    }

    public function setReportSequence($sequence)
    {
        $this->reportSequence = $sequence;

        return $this;
    }

    public function getReportSequence()
    {
        return $this->reportSequence;
    }

    public function setBenchmarkGroup(BenchmarkGroup $benchmarkGroup)
    {
        $this->benchmarkGroup = $benchmarkGroup;

        return $this;
    }

    public function setInputType($inputType)
    {
        $this->inputType = $inputType;

        return $this;
    }

    public function getInputType()
    {
        return $this->inputType;
    }

    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    public function getRequired()
    {
        return $this->required;
    }

    public function setComputed($computed)
    {
        $this->computed = $computed;

        return $this;
    }

    public function getComputed()
    {
        return $this->computed;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \Mrss\Entity\BenchmarkGroup
     */
    public function getBenchmarkGroup()
    {
        return $this->benchmarkGroup;
    }

    public function setYearsAvailable($years)
    {
        $this->yearsAvailable = implode(',', $years);

        return $this;
    }

    public function getYearsAvailable()
    {
        return array_map('intval', explode(',', $this->yearsAvailable));
    }

    public function isAvailableForYear($year)
    {
        $available = $this->getYearsAvailable();

        return in_array($year, $available);
    }

    public function setBenchmarkModel($model)
    {
        $this->benchmarkModel = $model;
    }

    public function setEquation($equation)
    {
        $this->equation = $equation;

        return $this;
    }

    public function getEquation()
    {
        return $this->equation;
    }

    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    public function setComputeAfter($computeAfter)
    {
        $this->computeAfter = $computeAfter;

        return $this;
    }

    public function getComputeAfter()
    {
        return $this->computeAfter;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setExcludeFromCompletion($excludeFromCompletion)
    {
        if (empty($excludeFromCompletion)) {
            $excludeFromCompletion = false;
        }

        $this->excludeFromCompletion = $excludeFromCompletion;

        return $this;
    }

    public function getExcludeFromCompletion()
    {
        return $this->excludeFromCompletion;
    }

    public function setIncludeInNationalReport($includeInNationalReport)
    {
        if (empty($includeInNationalReport)) {
            $includeInNationalReport = false;
        }

        $this->includeInNationalReport = $includeInNationalReport;

        return $this;
    }

    public function getIncludeInNationalReport()
    {
        return $this->includeInNationalReport;
    }


    /**
     * @param mixed $descriptiveReportLabel
     * @return $this
     */
    public function setDescriptiveReportLabel($descriptiveReportLabel)
    {
        $this->descriptiveReportLabel = $descriptiveReportLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescriptiveReportLabel()
    {
        if (empty($this->descriptiveReportLabel)) {
            return $this->getReportLabel();
        }


        return $this->descriptiveReportLabel;
    }

    /**
     * @param mixed $highIsBetter
     * @return $this
     */
    public function setHighIsBetter($highIsBetter)
    {
        $this->highIsBetter = $highIsBetter;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHighIsBetter()
    {
        return $this->highIsBetter;
    }

    /**
     * @param mixed $includeInBestPerformer
     * @return $this
     */
    public function setIncludeInBestPerformer($includeInBestPerformer)
    {
        $this->includeInBestPerformer = $includeInBestPerformer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIncludeInBestPerformer()
    {
        return $this->includeInBestPerformer;
    }

    /**
     * @param mixed $peerReportLabel
     * @return $this
     */
    public function setPeerReportLabel($peerReportLabel)
    {
        $this->peerReportLabel = $peerReportLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPeerReportLabel()
    {
        if (empty($this->peerReportLabel)) {
            return $this->getReportLabel();
        }

        return $this->peerReportLabel;
    }

    /**
     * @param mixed $reportLabel
     * @return $this
     */
    public function setReportLabel($reportLabel)
    {
        $this->reportLabel = $reportLabel;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReportLabel()
    {
        if (empty($this->reportLabel)) {
            return $this->getName();
        }

        return $this->reportLabel;
    }

    /**
     * @param mixed $reportWeight
     * @return $this
     */
    public function setReportWeight($reportWeight)
    {
        $this->reportWeight = $reportWeight;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReportWeight()
    {
        return $this->reportWeight;
    }

    /**
     * @param mixed $yearOffset
     * @return $this
     */
    public function setYearOffset($yearOffset)
    {
        $this->yearOffset = $yearOffset;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getYearOffset()
    {
        return $this->yearOffset;
    }

    /**
     * @param mixed $yearPrefix
     * @return $this
     */
    public function setYearPrefix($yearPrefix)
    {
        $this->yearPrefix = $yearPrefix;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getYearPrefix()
    {
        return $this->yearPrefix;
    }


    /**
     * Implement the FormElementProviderInterface
     *
     */
    public function getFormElement()
    {
        $element = array(
            'name' => $this->getDbColumn(),
            'allow_empty' => true,
            'options' => array(
                'label' => $this->getName(),
                'help-block' => $this->getDescription()
            ),
            'attributes' => array(
                'id' => $this->getDbColumn(),
                'class' => 'input-small input-' . $this->getInputType()
            )
        );

        // Radio type:
        if ($this->getInputType() == 'radio') {
            $element['type'] = 'Select';

            $options = explode("\n", $this->getOptions());
            $options = array_map('trim', $options);
            $options = array_combine($options, $options);
            $element['attributes']['options'] = $options;
            $element['options']['empty_option'] = '---';
        }

        // Checkboxes type:
        if ($this->getInputType() == 'checkboxes') {
            $element['type'] = 'MultiCheckbox';

            $options = $this->getOptions();
            $options = str_replace("\r", '', $options);
            $options = explode("\n", $options);
            $options = array_map('trim', $options);
            $options = array_combine($options, $options);
            $element['attributes']['options'] = $options;
            $element['options']['use_hidden_element'] = true;
            //$element['options']['empty_option'] = '---';
        }

        // Textarea
        if ($this->getInputType() == 'textarea') {
            $element['type'] = 'Textarea';
        }

        // Some HTML 5 validation
        if ($this->getInputType() == 'dollars' || $this->getInputType() == 'float') {
            $element['attributes']['pattern'] = '(\-)?\d+(\.\d+)?';
            $element['attributes']['title'] = 'Use the format 1234 or 1234.56';
        } elseif ($this->getInputType() == 'percent') {
            $element['attributes']['pattern'] = '\d+(\.\d+)?';
            $element['attributes']['title'] = 'Use the format 12, 12.3 or 12.34';
        } elseif ($this->getInputType() == 'wholepercent') {
            $element['attributes']['pattern'] = '\d+';
            $element['attributes']['title'] = 'Use a whole number (no decimals)';
        } elseif ($this->getInputType() == 'number' ||
            $this->getInputType() == 'wholedollars') {
            $element['attributes']['pattern'] = '\d+';
            $element['attributes']['title'] = 'Use the format 1234';
        }


            return $element;
    }

    public function getFormElementInputFilter()
    {
        $inputFilter =  array(
            'name' => $this->getDbColumn(),
            'allow_empty' => true,
            'validators' => array()
        );

        // Check the format
        if ($this->getInputType() == 'number') {
            $inputFilter['validators'][] = array('name' => 'Digits');
        } elseif ($this->getInputType() == 'dollars') {
            $inputFilter['validators'][] = array(
                'name' => 'Regex',
                'options' => array(
                    'pattern' => '/^(\-)?\d+\.?(\d\d)?$/',
                    'messages' => array(
                        'regexNotMatch' => 'Use the format 1234 or 1234.56'
                    )
                )
            );
        } elseif ($this->getInputType() == 'wholedollars') {
            $inputFilter['validators'][] = array(
                'name' => 'Regex',
                'options' => array(
                    'pattern' => '/^\d+$/',
                    'messages' => array(
                        'regexNotMatch' => 'Use the format 1234'
                    )
                )
            );
        } elseif ($this->getInputType() == 'float') {
            $inputFilter['validators'][] = array(
                'name' => 'Regex',
                'options' => array(
                    'pattern' => '/^\d+\.?(\d+)?$/',
                    'messages' => array(
                        'regexNotMatch' => 'Use the format 1234, 1234.5, 1234.56 '
                            . 'or 1234.567'
                    )
                )
            );
        } elseif ($this->getInputType() == 'percent') {
            $inputFilter['validators'][] = array(
                'name' => 'Regex',
                'options' => array(
                    'pattern' => '/^\d+\.?(\d+)?$/',
                    'messages' => array(
                        'regexNotMatch' => 'Use the format 12, 12.3, 12.34 '
                    )
                )
            );

            $inputFilter['validators'][] = array(
                'name' => 'Between',
                'options' => array(
                    'min' => 0,
                    'max' => 100
                )
            );
        } elseif ($this->getInputType() == 'wholepercent') {
            $inputFilter['validators'][] = array(
                'name' => 'Regex',
                'options' => array(
                    'pattern' => '/^\d+$/',
                    'messages' => array(
                        'regexNotMatch' => 'Use the format 12'
                    )
                )
            );

            $inputFilter['validators'][] = array(
                'name' => 'Between',
                'options' => array(
                    'min' => 0,
                    'max' => 100
                )
            );
        }

        return $inputFilter;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    public function getInputFilter()
    {
        if (empty($this->inputFilter)) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name' => 'name',
                        'required' => true,
                        'filters' => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim')
                        ),
                        'validators' => array(
                            array('name' => 'NotEmpty')
                        )
                    )
                )
            );


            // Validator to make sure the dbColumn is unique
            $repository = $this->getEntityManager()
                ->getRepository('Mrss\Entity\Benchmark');
            //var_dump(get_parent_class($repository));die;
            $dbColumnUniqueValidator = new \DoctrineModule\Validator\UniqueObject(
                array(
                    'object_repository' => $repository,
                    'object_manager' => $this->getEntityManager(),
                    'fields' => array('dbColumn'),
                    'messages' => array(
                        'objectNotUnique' => 'The database column must be unique.'
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name' => 'dbColumn',
                        'required' => true,
                        'filters' => array(
                            array('name' => 'StringTrim')
                        ),
                        'validators' => array(
                            array('name' => 'NotEmpty'),
                            // Allow dbColumn to be non-unique
                            //$dbColumnUniqueValidator
                        )
                    )
                )
            );

            /**
             * Equation validator. We need empty values to get processed by the
             * equation validator as they may be valid depending on whether
             * it's a computed value or not.
             * http://stackoverflow.com/questions/14910431/empty-values-passed-to-zend-framework-2-validators
             */
            if ($equationValidator = $this->getEquationValidator()) {
                $input = new \Zend\InputFilter\Input('equation');
                $input->getValidatorChain()
                    ->attach(
                        new \Zend\Validator\NotEmpty('null')
                    )
                    ->attach($equationValidator);

                $inputFilter->add($input);
            }

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    public function format($value, $decimalPlaces = null)
    {
        $formatted = $value;

        $prefix = $suffix = '';
        if ($this->isPercent()) {
            $suffix = '%';
        } elseif ($this->isDollars()) {
            $prefix = '$';
        }

        if (!is_null($value) &&
            $this->getInputType() != 'radio' &&
            $this->getInputType() != 'checkboxes') {

            if (null === $decimalPlaces) {
                $decimalPlaces = $this->getDecimalPlaces();
            }

            $formatted = $prefix .
                number_format($value, $decimalPlaces) .
                $suffix;
        }

        return $formatted;
    }

    public function getDecimalPlaces()
    {
        $dbColumn = $this->getDbColumn();

        $map = array(
            'enrollment_information_contact_hours_per_student' => 1,
            'enrollment_information_market_penetration' => 1,
            'fst_yr_gpa' => 2,
            'avrg_1y_crh' => 2,
            'n96_exp' => 1,
            'n97_ova_exp' => 1,
            'n98_enr_again' => 1,
            'ac_adv_coun' => 1,
            'ac_serv' => 1,
            'adm_fin_aid' => 1,
            'camp_clim' => 1,
            'camp_supp' => 1,
            'conc_indiv' => 1,
            'instr_eff' => 1,
            'reg_eff' => 1,
            'resp_div_pop' => 1,
            'safe_sec' => 1,
            'serv_exc' => 1,
            'stud_centr' => 1,
            'act_coll_learn' => 1,
            'stud_eff' => 1,
            'acad_chall' => 1,
            'stud_fac_int' => 1,
            'sup_learn' => 1,
            'choo_again' => 1,
            'ova_impr' => 1,
            'av_cred_sec_size' => 2,
            'griev_occ_rate' => 4,
            'harass_occ_rate' => 4,
            'stu_fac_ratio' => 2,
            'stud_inst_serv_ratio' => 2,
            'empl_inst_serv_ratio' => 2
        );

        $decimalPlaces = 0;
        if (isset($map[$dbColumn])) {
            $decimalPlaces = $map[$dbColumn];
        } else {
            //All NCCBP percentages should use 2 decimal places
            if ($this->getBenchmarkGroup()->getStudy()->getId() == 1 && $this->isPercent()) {
                $decimalPlaces = 2;
            }

            // Floats should get 2
            if ($this->getInputType() == 'float') {
                $decimalPlaces = 2;
            }
        }

        return $decimalPlaces;
    }

    public function getCompletionPercentages()
    {
        if (empty($this->completionPercentages)) {
            $this->completionPercentages = $this->benchmarkModel
                ->getCompletionPercentages(
                    $this->getDbColumn(),
                    $this->getYearsAvailable()
                );
        }

        return $this->completionPercentages;
    }

    public function getCompletionPercentage($year)
    {
        $years = $this->getCompletionPercentages();

        if (!empty($years[$year])) {
            $percentage = $years[$year];
        } else {
            $percentage = null;
        }

        return $percentage;
    }

    public function isPercent()
    {
        return ($this->getInputType() == 'percent'
            || $this->getInputType() == 'wholepercent');
    }

    public function isDollars()
    {
        return ($this->getInputType() == 'dollars'
            || $this->getInputType() == 'wholedollars');
    }

    public function getInputTypeAbbr()
    {
        $abbr = $this->getInputType();

        if ($this->isPercent()) {
            $abbr = '%';
        } elseif ($this->isDollars()) {
            $abbr = '$';
        } elseif ($abbr == 'number') {
            $abbr = '#';
        } elseif ($abbr == 'float') {
            $abbr = '#.#';
        }

        return $abbr;
    }

    public function getPrefix()
    {
        $prefix = null;

        if ($this->isDollars()) {
            $prefix = '$';
        }

        return $prefix;
    }

    public function getSuffix()
    {
        $suffix = null;

        if ($this->isPercent()) {
            $suffix = '%';
        }

        return $suffix;
    }

    public function setEquationValidator($equationValidator)
    {
        $this->equationValidator = $equationValidator;

        return $this;
    }

    public function getEquationValidator()
    {
        return $this->equationValidator;
    }

    public function setEntityManager($em)
    {
        $this->entityManager = $em;

        return $this;
    }

    public function getEntityManager()
    {
        return $this->entityManager;
    }
}
