<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Regex;

class OfferCode extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('offerCode');

        $this->add(
            array(
                'name' => 'id',
                'type' => 'Hidden'
            )
        );

        $this->add(
            array(
                'name' => 'study',
                'type' => 'Hidden',
            )
        );

        $this->add(
            array(
                'name' => 'code',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Offer Code'
                )
            )
        );

        $this->add(
            array(
                'name' => 'price',
                'type' => 'Text',
                'options' => array(
                    'label' => 'Price'
                )
            )
        );

        $this->add(
            array(
                'name' => 'skipOtherDiscounts',
                'type' => 'Checkbox',
                'options' => array(
                    'label' => 'Skip Other Discounts'
                )
            )
        );

        $this->add($this->getButtonFieldset());

        $this->setInputFilter($this->getInputFilterSetup());
    }

    public function getInputFilterSetup()
    {
        $filter = new InputFilter();

        // Code is required
        $code = new Input('code');
        $code->setRequired(true);
        $filter->add($code);

        // Price is required and must be a float
        $price = new Input('price');
        $price->setRequired(true);

        $validator = new Regex('/^\d+\.?(\d+)?$/');
        $price->getValidatorChain()->attach($validator);
        $filter->add($price);

        return $filter;

        /*
        $filter->add(
            array(
                'code' => array
            )
        )
        return array(
            'code' => array(
                'required' => true
            ),
            'price' => array(
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Regex',
                        'options' => array(
                            'pattern' => '/^\d+\.?(\d+)?$/',
                            'messages' => array(
                                'regexNotMatch' => 'Use the format 1234 or'
                                    . ' 1234.56 '

                            )
                        )
                    )
                )
            )
        );*/
    }
}
