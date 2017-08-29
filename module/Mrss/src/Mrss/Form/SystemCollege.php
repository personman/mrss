<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Mrss\Model\College;

/**
 * Class SystemCollege
 *
 * This form is for making associations between colleges and systems
 *
 * @package Mrss\Form
 */
class SystemCollege extends AbstractForm
{
    protected $collegeModel;

    public function __construct(College $collegeModel, $years)
    {
        $this->collegeModel = $collegeModel;

        // Call the parent constructor
        parent::__construct('system_college');

        $this->add(
            array(
                'name' => 'system_id',
                'type' => 'Hidden'
            )
        );

        $this->add(
            array(
                'name' => 'college_id',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Institution'
                ),
                'attributes' => array(
                    'options' => $this->getCollegeOptions()
                )
            )
        );

        $combined = array_combine($years, $years);
        $this->add(
            array(
                'name' => 'years',
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'options' => array(
                    'label' => 'Years',
                    'value_options' => $combined
                )
            )
        );

        $this->add($this->getButtonFieldset());
    }

    public function getCollegeOptions()
    {
        $colleges = $this->collegeModel->findAll();
        $options = array();

        foreach ($colleges as $college) {
            $options[$college->getId()] = $college->getName();
        }

        return $options;
    }

    public function getInputFilter()
    {
        $filter = parent::getInputFilter();
        $filter->get('years')->setRequired(false);

        //pr($filter);
        return $filter;
    }
}
