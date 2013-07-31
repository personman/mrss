<?php

namespace Mrss\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Form\Fieldset;
use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use Doctrine\Common\Persistence\ObjectManager;

class Page extends Form implements ObjectManagerAwareInterface
{
    protected $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->setObjectManager($objectManager);

        // Call the parent constructor
        parent::__construct('page');

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden'
            )
        );

        $this->add(
            array(
                'name' => 'title',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Title'
                )
            )
        );

        $this->add(
            array(
                'name' => 'route',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Route'
                )
            )
        );

        $this->add(
            array(
                'name' => 'content',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Content'
                ),
                'attributes' => array(
                    'rows' => 8,
                    'id' => 'wysiwygContent'
                )
            )
        );


        $this->add(
            array(
                'name' => 'status',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Status'
                ),
                'attributes' => array(
                    'options' => array(
                        'published' => 'Published',
                        'draft' => 'Draft'
                    )
                )
            )
        );

        $this->add(
            array(
                'type' => 'DoctrineModule\Form\Element\ObjectMultiCheckbox',
                'name' => 'studies',
                'options' => array(
                    'label' => 'Studies',
                    'object_manager' => $this->getObjectManager(),
                    'target_class' => 'Mrss\Entity\Study',
                    'property' => 'name'
                )
            )
        );

        $this->add($this->getButtonFieldset());
    }

    public function getButtonFieldset()
    {
        // Fieldset for buttons
        $buttons = new Fieldset('buttons');
        $buttons->setAttribute('class', 'well well-small');

        // Add the save button
        $save = new Element\Submit('submit');
        $save->setValue('Save');
        $save->setAttribute('class', 'btn btn-primary');
        $buttons->add($save);

        // Add the delete button
        $delete = new Element\Submit('delete');
        $delete->setValue('Delete');
        $delete->setAttribute('class', 'btn btn-danger');
        $delete->setAttribute('onClick', "return confirm('Are you sure?')");
        $buttons->add($delete);

        return $buttons;
    }

    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
