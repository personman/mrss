<?php

namespace Mrss\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Form\Fieldset;

class AbstractForm extends Form
{

    public function __construct($name)
    {
        parent::__construct($name);
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('method', 'post');
    }

    /**
     * Standard save button
     *
     * @return Fieldset
     */
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

        return $buttons;
    }
}
