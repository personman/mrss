<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\Form\Element;

class Exceldiff extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('exceldiff');


        $this->add(
            array(
                'name' => 'excel_file',
                'type' => 'File',
                'options' => array(
                    'label' => 'Excel File',
                    'help-block' => 'This file should have the full list of email
                    addresses in column A and the addresses to be removed in
                    column B. Column C should be empty. The output file will have
                    the new list in column C.'
                ),
                'attributes' => array(
                    'id' => 'excel_file'
                )
            )
        );


        $this->add($this->getButtonFieldset('Process'));
    }
}
