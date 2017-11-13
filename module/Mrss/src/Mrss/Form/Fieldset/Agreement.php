<?php

namespace Mrss\Form\Fieldset;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Validator\Digits;
use Zend\Validator\InArray;

class Agreement extends Fieldset implements InputFilterProviderInterface
{
    protected $offerCodes = array();
    protected $studyName;

    public function __construct($studyName = 'MRSS', $offerCodes = array())
    {
        $this->studyName = $studyName;
        // Case insensitive search
        $this->offerCodes = array_map('strtolower', $offerCodes);

        parent::__construct('agreement');

        $this->addSignatureElements();

        if (!empty($offerCodes)) {
            $this->add(
                array(
                    'name' => 'offerCode',
                    'type' => 'Text',
                    'options' => array(
                        'label' => 'Offer Code',
                    ),
                    'attributes' => array(
                        'id' => 'offerCode'
                    )
                )
            );
        }

    }

    protected function addSignatureElements()
    {
        $this->add(
            array(
                'name' => 'agree',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Agreement',
                    'help-block' => 'I agree to the terms above',
                    'checked_value' => 1,
                    'unchecked_value' => 'no'
                ),
                'attributes' => array(
                    //'required' => true,
                    'id' => 'agree',
                    'description' => 'test'
                )
            )
        );

        $this->add(
            array(
                'name' => 'signature',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Electronic Signature',
                    'help-block' => 'The name of the person at your institution ' .
                        'who is authorizing participation'
                ),
                'attributes' => array(
                    //'required' => true,
                    'id' => 'signature'
                )
            )
        );

        $this->add(
            array(
                'name' => 'title',
                'type' => 'Text',
                'required' => true,
                'options' => array(
                    'label' => 'Title'
                ),
                'attributes' => array(
                    //'required' => true,
                    'id' => 'subscriber-title'
                )
            )
        );

        $this->add(
            array(
                'name' => 'authorization',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Authorization',
                    'help-block' => 'I hereby authorize my institution\'s
                    participation in the ' . $this->studyName,
                    'checked_value' => 1,
                    'unchecked_value' => 'no'
                ),
                'attributes' => array(
                    //'required' => true,
                    'id' => 'authorization'
                )
            )
        );
    }

    protected function getBasicFilters()
    {
        return array(
            'agree' => array(
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Digits',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                Digits::NOT_DIGITS => 'You must agree to the terms to
                                subscribe'
                            )
                        )
                    )
                )
            ),
            'signature' => array(
                'required' => true
            ),
            'title' => array(
                'required' => true
            ),
            'authorization' => array(
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Digits',
                        'break_chain_on_failure' => true,
                        'options' => array(
                            'messages' => array(
                                Digits::NOT_DIGITS => 'You must check the
                                authorization checkbox to continue'
                            )
                        )
                    )
                )
            ),
        );
    }

    /**
     * Form validation
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        $filters = $this->getBasicFilters();

        // See if the offer code is valid
        if (!empty($this->offerCodes)) {
            $filters['offerCode'] = array(
                'required' => false,
                'filters' => array(
                    array(
                        'name' => 'StringToLower'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'InArray',
                        'options' => array(
                            'haystack' => $this->offerCodes,
                            'messages' => array(
                                'notInArray' => 'That offer code is not valid. ' .
                                    'Please try again or leave it blank.'
                            )
                        )
                    )
                )
            );
        }

        return $filters;
    }
}
