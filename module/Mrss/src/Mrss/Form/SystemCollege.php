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

    public function __construct(College $collegeModel)
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
}
