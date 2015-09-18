<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Mrss\Form\Fieldset\User as UserFieldset;
use Zend\Form\Fieldset;
use Zend\Validator;
use Zend\Filter;
use Zend\Form\Element;

class SubscriptionFree extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('subscription-free');

        $this->add(
            array(
                'name' => 'name',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Find Your Institution',
                    'help-block' => 'Search by Institution Name, OPE ID, or IPEDS Unit ID'
                ),
                'attributes' => array(
                    'id' => 'search-field'
                )
            )
        );

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden',
                'attributes' => array(
                    'id' => 'search-id'
                )

            )
        );

        $fieldset = new UserFieldset('user');
        $fieldset->setLabel('User Information');
        $this->add($fieldset);

        $this->add($this->getButtonFieldset('Continue'));
    }
}
