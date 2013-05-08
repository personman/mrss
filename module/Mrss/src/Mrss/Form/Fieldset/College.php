<?php

namespace Mrss\Form\Fieldset;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\I18n\Validator\PostCode;
use Zend\Validator\Regex;

class College extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('institution');

        $this->setLabel('Institution');

        $this->add(
            array(
                'name' => 'name',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Name of Institution'
                )
            )
        );

        $this->add(
            array(
                'name' => 'ipeds',
                'type' => 'Text',
                'options' => array(
                    'label' => 'IPEDS Unit ID'
                )
            )
        );

        $this->add(
            array(
                'name' => 'address',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Address'
                )
            )
        );

        $this->add(
            array(
                'name' => 'address2',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Address 2'
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
                )
            )
        );

        $this->add(
            array(
                'name' => 'state',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'State'
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
                )
            )
        );
    }

    public function getInputFilterSpecification()
    {
        //$validator = new PostCode('en_US');
        $zipValidator = new Regex(array('pattern' => '/^\d{5}(?:[-\s]\d{4})?$/'));
        $zipValidator->setMessage(
            'Zip code should be in the format "12345" or "12345-6789"',
            Regex::NOT_MATCH
        );

        return array(
            'name' => array(
                'required' => true
            ),
            'ipeds' => array(
                'required' => true
            ),
            'address' => array(
                'required' => true
            ),
            'city' => array(
                'required' => true
            ),
            'state' => array(
                'required' => true
            ),
            'zip' => array(
                'required' => true,
                'validators' => array(
                    // This requires the intl extension
                    //new PostCode(array('locale' => 'en_US'))
                    $zipValidator
                )
            )
        );
    }
}
