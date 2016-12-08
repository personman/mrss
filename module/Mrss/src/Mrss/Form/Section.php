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
                    'label' => 'Price Add-on',
                    'help-block' => 'This number gets added to the relevant study base price
                    (renewal, regular, or early) when this is the only module selected.'
                )
            )
        );

        $this->add(
            array(
                'name' => 'comboPrice',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Combo Price Add-on',
                    'help-block' => 'This number gets added to the relevant study base price
                    (renewal, regular, or early) when this module is selected along with at least one other module.'
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
