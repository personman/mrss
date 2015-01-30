<?php

namespace Mrss\Form;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\Form\Fieldset;

class AbstractForm extends Form
{

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
        $includeDelete = false
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
            $buttons->add($reset);
        }

        if ($includeDelete) {
            // Add the delete button
            $delete = new Element\Submit('delete');
            $delete->setValue('Delete');
            $delete->setAttribute('class', 'btn btn-danger');
            //$delete->setLabel('Delete');
            $delete->setAttribute('id', 'deleteButton');
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

    public function addName()
    {
        $this->add(
            array(
                'name' => 'name',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Name'
                )
            )
        );
    }

    public function addDescription()
    {
        $this->add(
            array(
                'name' => 'description',
                'type' => 'Textarea',
                'options' => array(
                    'label' => 'Description'
                ),
                'attributes' => array(
                    'rows' => 8
                )
            )
        );
    }

    public function getStates($includeBlankOption = true)
    {
        $states = array(
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

        if ($includeBlankOption) {
            $blankOption = array(
                '' => 'Select State'
            );

            $states = array_merge($blankOption, $states);
        }

        return $states;
    }
}
