<?php

namespace Mrss\Service;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Mrss\Entity\FormFieldsetProviderInterface as FieldsetProvider;
use Mrss\Entity\FormElementProviderInterface as FormElementProvider;
use Zend\InputFilter\InputFilter;

class FormBuilder
{
    /**
     * the buildForm method
     *
     * The provider is any object that implements the FieldsetProvider interface
     *
     * @param \Mrss\Entity\FormFieldsetProviderInterface $provider
     * @param $year
     * @return \Zend\Form\Form
     */
    public function buildForm(FieldsetProvider $provider, $year)
    {
        $form = new Form;
        $inputFilter = new InputFilter();

        $this->addSubObservationFields($provider, $form);

        // Add the elements from the provider
        foreach ($provider->getElements($year) as $elementProvider) {
            $element = $this->getElement($elementProvider);
            $form->add($element);
            $inputFilter->add($elementProvider->getFormElementInputFilter());
        }

        $buttons = new Fieldset('buttons');
        $buttons->setAttribute('class', 'well well-small');
        $buttons->setLabel('');

        // Add the save button
        $save = new Element\Submit('submit');
        $save->setValue('Save');
        $save->setAttribute('class', 'btn btn-primary');
        $buttons->add($save);

        $form->add($buttons);
        //echo '<pre>'; print_r($inputFilter);
        $form->setInputFilter($inputFilter);


        return $form;
    }

    public function getElement(FormElementProvider $elementProvider)
    {
        return $elementProvider->getFormElement();
    }

    public function addSubObservationFields($provider, $form)
    {
        if ($provider->getUseSubObservation()) {
            $name = new Element\Text('name');
            $name->setLabel('Academic Unit Name');
            $form->add($name);

            // Add the id, too
            $id = new Element\Hidden('id');
            $form->add($id);
        }
    }
}
