<?php

namespace Mrss\Service;

use Mrss\Entity\Observation;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\PropertyGenerator;

class ObservationGenerator
{
    protected $study;

    public function generate()
    {
        $this->checkForMissingFields();

        $properties = $this->getProperties();

        $docblock = DocBlockGenerator::fromArray(array(
            'shortDescription' => 'Observation entity class generated by ObservationGenerator',
            'longDescription'  => '',
            'tags'             => array(
                array(
                    'name'        => 'ORM\Entity',
                ),
                array(
                    'name'        => '@ORM\Table(name="observations")',
                ),
            ),
        ));

        $class = new ClassGenerator();
        $class->setName('Observation')
            ->setExtendedClass('ObservationBase')
            ->setNamespaceName('Mrss\Entity')
            ->addUse('Doctrine\ORM\Mapping', 'ORM')
            ->addUse('Mrss\Entity\Exception')
            ->setDocBlock($docblock);


        foreach ($properties as $property) {
            $class->addPropertyFromGenerator($property);
        }

        pr($class->generate());

        die('test');
    }

    protected function checkForMissingFields()
    {
        $observation = new Observation;
        $props = array_keys(get_object_vars($observation));

        $dbColumns = array();
        foreach ($this->getStudy()->getAllBenchmarks() as $benchmark) {
            $dbColumns[] = $benchmark->getDbColumn();
        }

        $diff = array_diff($dbColumns, $props);

        if ($diff) {
            echo 'Props in database, but not Observation:';
            pr($diff);
        }
    }

    public function setStudy($study)
    {
        $this->study = $study;

        return $this;
    }

    /**
     * @return \Mrss\Entity\Study
     */
    public function getStudy()
    {
        return $this->study;
    }

    protected function getProperties()
    {
        $properties = $this->getBaseProperties();
        foreach ($this->getStudy()->getAllBenchmarks() as $benchmark) {
            $type = $this->getColumnType($benchmark->getInputType());

            $pg = PropertyGenerator::fromArray(array(
                'name' => $benchmark->getDbColumn(),
                'flags' => array(PropertyGenerator::FLAG_PROTECTED),
                'docblock' => array(
                    'tags' => array(
                        array(
                            'name' => 'ORM\Column(type="' . $type . '", nullable=true)',
                        )
                    )
                ),
            ));

            $properties[$benchmark->getDbColumn()] = $pg;
        }

        return $properties;
    }

    protected function getBaseProperties()
    {
        $baseProperties = array();

        // Id
        $pg = PropertyGenerator::fromArray(array(
            'name' => 'id',
            'flags' => array(PropertyGenerator::FLAG_PROTECTED),
            'docblock' => array(
                'tags' => array(
                    array(
                        'name' => 'ORM\Id',
                    ),
                    array(
                        'name' => 'ORM\GeneratedValue(strategy="AUTO")',
                    ),
                    array(
                        'name' => 'ORM\Column(type="integer")',
                    )
                )
            ),
        ));
        $baseProperties[] = $pg;

        // Year
        $pg = PropertyGenerator::fromArray(array(
            'name' => 'year',
            'flags' => array(PropertyGenerator::FLAG_PROTECTED),
            'docblock' => array(
                'tags' => array(
                    array(
                        'name' => 'ORM\Column(type="integer")',
                    )
                )
            ),
        ));
        $baseProperties[] = $pg;

        // Cip Code
        $pg = PropertyGenerator::fromArray(array(
            'name' => 'cipCode',
            'flags' => array(PropertyGenerator::FLAG_PROTECTED),
            'docblock' => array(
                'tags' => array(
                    array(
                        'name' => 'ORM\Column(type="float")',
                    )
                )
            ),
        ));
        $baseProperties[] = $pg;

        // College
        $pg = PropertyGenerator::fromArray(array(
            'name' => 'college',
            'flags' => array(PropertyGenerator::FLAG_PROTECTED),
            'docblock' => array(
                'tags' => array(
                    array(
                        'name' => 'ORM\ManyToOne(targetEntity="College", inversedBy="observations")',
                    )
                )
            ),
        ));
        $baseProperties[] = $pg;

        // SubObservations
        $pg = PropertyGenerator::fromArray(array(
            'name' => 'subObservations',
            'flags' => array(PropertyGenerator::FLAG_PROTECTED),
            'docblock' => array(
                'tags' => array(
                    array(
                        'name' => 'ORM\OneToMany(targetEntity="SubObservation", mappedBy="observation")',
                    ),
                    array(
                        'name' => 'ORM\OrderBy({"id" = "ASC"})',
                    ),
                    array(
                        'name' => 'var SubObservation[]',
                    )
                )
            ),
        ));
        $baseProperties[] = $pg;


        // Subscriptions
        $pg = PropertyGenerator::fromArray(array(
            'name' => 'subscriptions',
            'flags' => array(PropertyGenerator::FLAG_PROTECTED),
            'docblock' => array(
                'tags' => array(
                    array(
                        'name' => 'ORM\OneToMany(targetEntity="Subscription", mappedBy="observation")',
                    ),
                    array(
                        'name' => 'var Subscription[]',
                    )
                )
            ),
        ));
        $baseProperties[] = $pg;

        return $baseProperties;
    }

    protected function getColumnType($inputType)
    {
        $colType = 'float';

        if ($inputType == 'radio' || $inputType == 'text') {
            $colType = 'string';
        } elseif ($inputType == 'textarea') {
            $colType = 'text';
        } elseif ($inputType == 'number') {
            $colType = 'integer';
        }

        return $colType;
    }

}
