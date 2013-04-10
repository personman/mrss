<?php

namespace Mrss\Service;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Mrss\Entity\FormFieldsetProviderInterface as FieldsetProvider;
use Mrss\Entity\FormElementProviderInterface as FormElementProvider;

class FormBuilder
{
    /**
     * the buildForm method
     *
     * The provider is any object that implements the FieldsetProvider interface
     *
     * @var FieldsetProvider $provider
     * @return \Zend\Form\Form
     */
    public function buildForm(FieldsetProvider $provider)
    {
        $form = new Form;

        // Add the elements from the provider
        foreach ($provider->getElements() as $elementProvider) {
            $element = $this->getElement($elementProvider);
            $form->add($element);
        }

        $buttons = new Fieldset('buttons');
        $buttons->setAttribute('class', 'well well-small');
        $buttons->setLabel('Submit Fieldset');

        // Add the save button
        $save = new Element\Submit('submit');
        $save->setValue('Save');
        $save->setAttribute('class', 'btn btn-primary');
        $buttons->add($save);

        $form->add($buttons);


        return $form;
    }

    public function getElement(FormElementProvider $elementProvider)
    {
        return $elementProvider->getFormElement();
    }
}
