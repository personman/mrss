<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\Form\Fieldset;
use Zend\Form\Element;

/**
 * Class ImportData
 *
 * Form for uploading Excel file to import data
 *
 * @package Mrss\Form
 */
class ImportData extends AbstractForm
{
    public function __construct($name)
    {
        parent::__construct($name);

        // Upload field
        // File Input
        $file = new Element\File('file');
        $file->setLabel('Excel file')
            ->setAttribute('id', 'file');
        $this->add($file);

        // Submit button
        $this->add(
            $this->getSubmitFieldset()
        );
    }


    public function getSubmitFieldset()
    {
        $fieldset = new Fieldset('submit');
        //$fieldset->setAttribute('class', 'well');

        $fieldset->add(
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'attributes' => array(
                    'class' => 'btn btn-primary',
                    'value' => 'Upload'
                )
            )
        );

        return $fieldset;
    }
}
