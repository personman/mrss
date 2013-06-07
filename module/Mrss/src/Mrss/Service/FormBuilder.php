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
     * @param \Mrss\Entity\FormFieldsetProviderInterface $provider
     * @param $year
     * @return \Zend\Form\Form
     */
    public function buildForm(FieldsetProvider $provider, $year)
    {
        $form = new Form;

        // Add the elements from the provider
        foreach ($provider->getElements($year) as $elementProvider) {
            $element = $this->getElement($elementProvider);
            $form->add($element);
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


        return $form;
    }

    public function getElement(FormElementProvider $elementProvider)
    {
        return $elementProvider->getFormElement();
    }
}
