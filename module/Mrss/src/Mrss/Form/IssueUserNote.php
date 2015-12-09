<?php

namespace Mrss\Form;

use Zend\Form\Element;

class IssueUserNote extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('issueUserNote');

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden'
            )
        );

        $this->add(
            array(
                'name' => 'userNote',
                'type' => 'Textarea',
                'options' => array(
                )
            )
        );

        $buttons = $this->getButtonFieldset();

        // Add the cancel button
        $cancel = new Element\Submit('cancel');
        $cancel->setValue('Cancel');
        $cancel->setAttribute('class', 'btn btn-danger');
        $cancel->setAttribute('id', 'cancelButton');
        $buttons->add($cancel);

        $this->add($buttons);
    }
}
