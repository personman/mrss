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

    public function addCurrentYear()
    {
        $this->add(
            array(
                'name' => 'currentYear',
                'type' => 'Zend\Form\Element\Select',
                'options' => array(
                    'label' => 'Current Year'
                ),
                'attributes' => array(
                    'options' => $this->getYearsAvailable()
                )
            )
        );
    }

    public function getYearsAvailable()
    {
        $range = range(2006, date('Y') + 3);
        $combined = array_combine($range, $range);

        return $combined;
    }

    protected function addOpenClosedElements()
    {
        $this->add(
            array(
                'name' => 'enrollmentOpen',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Enrollment Open'
                )
            )
        );

        $this->add(
            array(
                'name' => 'dataEntryOpen',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Data Entry Open'
                )
            )
        );

        $this->add(
            array(
                'name' => 'outlierReportsOpen',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Outlier Reports Open'
                )
            )
        );

        $this->add(
            array(
                'name' => 'reportsOpen',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Reports Open'
                )
            )
        );
    }

    protected function addAddressFields()
    {
        $this->add(
            array(
                'name' => 'address',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Address'
                ),
                'attributes' => array(
                    'id' => 'address'
                )
            )
        );

        $this->add(
            array(
                'name' => 'address2',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Address 2'
                ),
                'attributes' => array(
                    'id' => 'address2'
                )
            )
        );

        $this->add(
            array(
                'name' => 'city',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'City'
                ),
                'attributes' => array(
                    'id' => 'city'
                )
            )
        );

        $this->add(
            array(
                'name' => 'state',
                'type' => 'Select',
                'required' => true,
                'options' => array(
                    'label' => 'State'
                ),
                'attributes' => array(
                    'options' => $this->getStates(),
                    'id' => 'state'
                )
            )
        );

        $this->add(
            array(
                'name' => 'zip',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'Zip Code'
                ),
                'attributes' => array(
                    'id' => 'zip'
                )
            )
        );
    }
}
