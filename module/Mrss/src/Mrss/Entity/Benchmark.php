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
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\Column(type="string")
     */
    protected $dbColumn;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sequence;

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
     * @ORM\Column(type="string", nullable=true)
     */
    protected $options;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    protected $equation;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $excludeFromCompletion;

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

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
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

            $options = explode(',', $this->getOptions());
            $options = array_combine($options, $options);
            $element['attributes']['options'] = $options;
            $element['options']['empty_option'] = '---';
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

    public function getPrefix()
    {
        $prefix = '';
        if ($this->isDollars()) {
            $prefix = '$';
        }

        return $prefix;
    }

    public function getSuffix()
    {
        $suffix = '';
        if ($this->isPercent()) {
            $suffix = '%';
        }

        return $suffix;
    }
}
