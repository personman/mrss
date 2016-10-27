<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\Validator\EmailAddress;

class Email extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('email');

        $this->add(
            array(
                'name' => 'to',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Recipient Email'
                ),
                'attributes' => array(
                    'id' => 'to'
                )
            )
        );

        $this->add(
            array(
                'name' => 'subject',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Subject'
                ),
                'attributes' => array(
                    'id' => 'subject'
                )
            )
        );

        $this->add(
            array(
                'name' => 'body',
                'type' => 'Textarea',
                'required' => true,
                'options' => array(
                    'label' => 'Message'
                ),
                'attributes' => array(
                    'id' => 'body'
                )
            )
        );

        $this->add($this->getButtonFieldset('Send'));
    }

    public function getInputFilter()
    {
        $filter = parent::getInputFilter();

        $emailValidator = new EmailAddress();

        $filter->get('to')->setRequired(true);
        $filter->get('to')->getValidatorChain()->attach($emailValidator);

        $filter->get('subject')->setRequired(true);
        $filter->get('body')->setRequired(true);

        //pr($filter);
        return $filter;
    }
}
