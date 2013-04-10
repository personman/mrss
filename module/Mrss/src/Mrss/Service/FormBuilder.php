<?php

namespace Mrss\Service;

use Zend\Form\Form;
use Mrss\Entity\FormFieldsetProviderInterface as FieldsetProvider;
use Mrss\Entity\FormElementProviderInterface as FormElementProvider;

class FormBuilder
{
    /**
     * the buildForm method
     *
     * The provider is any object that implements the FieldsetProvider interface
     **/
    public function buildForm(FieldsetProvider $provider)
    {
        $form = new Form;

        // Add the elements from the provider
        foreach ($provider->getElements() as $elementProvider) {
            $element = $this->getElement($elementProvider);
            $form->add($element);
        }

        // Add the save button
        $save = new \Zend\Form\Element\Submit('submit');
        $save->setValue('Save');
        $save->setAttribute('class', 'btn');
        $form->add($save);


        return $form;
    }

    public function getElement(FormElementProvider $elementProvider)
    {
        return $elementProvider->getFormElement();
    }
}
