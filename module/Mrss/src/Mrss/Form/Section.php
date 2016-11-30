<?php

namespace Mrss\Form;
use Doctrine\Common\Persistence\ObjectManager;

class Section extends ObjectManagerAwareAbstractForm
{
    public function __construct($objectManager)
    {
        $this->setObjectManager($objectManager);

        // Call the parent constructor
        parent::__construct('section');
        $this->addBasicFields();
        $this->addBenchmarkGroups();
        $this->add($this->getButtonFieldset());
    }

    protected function addBasicFields()
    {
        $this->addId();
        $this->addName();
        $this->addDescription();


        $this->add(
            array(
                'name' => 'price',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Price'
                )
            )
        );
    }

    protected function addBenchmarkGroups()
    {
        $this->add(
            array(
                'type' => 'DoctrineModule\Form\Element\ObjectMultiCheckbox',
                'name' => 'benchmarkGroups',
                'options' => array(
                    'label' => 'Forms',
                    'object_manager' => $this->getObjectManager(),
                    'target_class' => 'Mrss\Entity\BenchmarkGroup',
                    'property' => 'name',
                ),
                'attributes' => array(
                    'id' => 'benchmarkGroups'
                )
            )
        );
    }
}
