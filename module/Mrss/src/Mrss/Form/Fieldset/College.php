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
                'type' => 'Select',
                'required' => true,
                'options' => array(
                    'label' => 'State'
                ),
                'attributes' => array(
                    'options' => $this->getStates()
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
        $ipedsValidator = new Regex(array('pattern' => '/^\d{6}$/'));
        $ipedsValidator->setMessage(
            'Use the format "123456"',
            Regex::NOT_MATCH
        );


        $zipValidator = new Regex(array('pattern' => '/^\d{5}(?:[-\s]\d{4})?$/'));
        $zipValidator->setMessage(
            'Use the format "12345" or "12345-6789"',
            Regex::NOT_MATCH
        );

        return array(
            'name' => array(
                'required' => true
            ),
            'ipeds' => array(
                'required' => true,
                'validators' => array(
                    $ipedsValidator
                )
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
    
    public function getStates()
    {
        return array(
            '' => 'Select State',
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District Of Columbia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming'
        );
    }
}
