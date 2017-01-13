<?php

namespace Mrss\Form\Fieldset;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Regex;

class College extends Fieldset implements InputFilterProviderInterface
{
    public function __construct($includeExec = false)
    {
        parent::__construct('institution');

        $this->setLabel('Institution');

        $this->addBasicFields();

        if ($includeExec) {
            $this->addExecutiveFields();

        }
    }

    protected function addBasicFields()
    {
        $this->add(
            array(
                'name' => 'name',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Name of Institution'
                ),
                'attributes' => array(
                    'id' => 'institution-name'
                )
            )
        );

        $this->add(
            array(
                'name' => 'abbreviation',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Abbreviation for Institution'
                ),
                'attributes' => array(
                    'id' => 'institution-abbreviation'
                )
            )
        );

        $this->add(
            array(
                'name' => 'ipeds',
                'type' => 'Text',
                'options' => array(
                    'label' => 'IPEDS Unit ID'
                ),
                'attributes' => array(
                    'id' => 'institution-ipeds'
                )
            )
        );

        $this->add(
            array(
                'name' => 'opeId',
                'type' => 'Text',
                'options' => array(
                    'label' => 'OPE ID'
                ),
                'attributes' => array(
                    'id' => 'institution-opeid'
                )
            )
        );

        $this->add(
            array(
                'name' => 'address',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Address'
                ),
                'attributes' => array(
                    'id' => 'institution-address'
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
                    'id' => 'institution-address2'
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
                    'id' => 'institution-city'
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
                    'id' => 'institution-state'
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
                    'id' => 'institution-zip'
                )
            )
        );

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden',
            )
        );
    }

    protected function addExecutiveFields()
    {

        $this->add(
            array(
                'name' => 'execTitle',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'Executive Title',
                    'help-block' => 'For example: President or Chancellor'
                ),
            )
        );

        $this->add(
            array(
                'name' => 'execSalutation',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Prefix'
                ),
                'attributes' => array(
                    'options' => array(
                        '' => 'Select Prefix',
                        'Dr.' => 'Dr.',
                        'Mr.' => 'Mr.',
                        'Ms.' => 'Ms.'
                    )
                )
            )
        );


        $this->add(
            array(
                'name' => 'execFirstName',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'First Name'
                ),
            )
        );

        $this->add(
            array(
                'name' => 'execLastName',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'Last Name'
                ),
            )
        );

        $this->add(
            array(
                'name' => 'execEmail',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'E-Mail Address'
                ),
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
