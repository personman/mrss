<?php

namespace Mrss\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Form\Fieldset;

class AbstractForm extends Form
{
    protected $includeCanada = false;

    public function __construct($name)
    {
        parent::__construct($name);
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('method', 'post');
    }

    /**
     * Standard save button
     *
     * @param string $buttonText
     * @param bool $includeReset
     * @param bool $includeDelete
     * @return Fieldset
     */
    public function getButtonFieldset(
        $buttonText = 'Save',
        $includeReset = false,
        $includeDelete = false,
        $confirm = 'Are you sure?'
    ) {
        // Fieldset for buttons
        $buttons = new Fieldset('buttons');
        $buttons->setAttribute('class', 'well well-small');

        // Add the save button
        $save = new Element\Submit('submit');
        $save->setValue($buttonText);
        $save->setAttribute('class', 'btn btn-primary');
        $save->setAttribute('id', 'submitButton');
        $buttons->add($save);

        if ($includeReset) {
            // Add the reset button
            $reset = new Element\Button('reset');
            $reset->setValue('Clear');
            $reset->setLabel('Clear');
            $reset->setAttribute('class', 'btn btn-danger formClearButton');
            $reset->setAttribute('id', 'resetButton');
            $buttons->add($reset);
        }

        if ($includeDelete) {
            // Add the delete button
            $delete = new Element\Submit('delete');
            $delete->setValue('Delete');
            $delete->setAttribute('class', 'btn btn-danger');
            //$delete->setLabel('Delete');
            $delete->setAttribute('id', 'deleteButton');
            $confirm = addslashes($confirm);
            $delete->setAttribute('onClick', "return confirm('$confirm')");
            $buttons->add($delete);
        }

        return $buttons;
    }

    public function addRedirect($redirect)
    {
        // Redirect to renew if needed
        if (!empty($redirect)) {
            $this->add(
                array(
                    'name' => 'redirect',
                    'type' => 'Hidden',
                    'attributes' => array(
                        'value' => $redirect
                    )
                )
            );
        }
    }

    public function addId()
    {
        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden'
            )
        );
    }

    public function addName($label = 'Name', $helpBlock = null, $required = false)
    {
        $field = array(
            'name' => 'name',
            'type' => 'Text',
            'required' => $required,
            'options' => array(
                'label' => $label
            )
        );

        if (!empty($helpBlock)) {
            $field['options']['help-block'] = $helpBlock;
        }

        $this->add($field);
    }

    public function addDescription($label = 'Description', $helpBlock = null)
    {
        $field = array(
            'name' => 'description',
            'type' => 'Textarea',
            'options' => array(
                'label' => $label
            ),
            'attributes' => array(
                'rows' => 8,
                'id' => 'description'
            )
        );

        if (!empty($helpBlock)) {
            $field['options']['help-block'] = $helpBlock;
        }

        $this->add($field);
    }

    public function getStates($includeBlankOption = true)
    {
        return getStates($includeBlankOption, $this->getIncludeCanada());
    }

    public function setIncludeCanada($include)
    {
        $this->includeCanada = $include;

        return $this;
    }

    public function getIncludeCanada()
    {
        return $this->includeCanada;
    }
}
