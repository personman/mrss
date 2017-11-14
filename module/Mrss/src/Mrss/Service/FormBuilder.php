<?php

namespace Mrss\Service;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Mrss\Entity\FormFieldsetProviderInterface as FieldsetProvider;
use Mrss\Entity\FormElementProviderInterface as FormElementProvider;
use Mrss\Entity\Benchmark;
use Zend\InputFilter\InputFilter;

class FormBuilder
{
    protected $variableSubstitutionService;

    protected $lastYearObservation;

    /**
     * the buildForm method
     *
     * The provider is any object that implements the FieldsetProvider interface
     *
     * @param \Mrss\Entity\FormFieldsetProviderInterface $provider
     * @param $year
     * @param bool $disabled
     * @return \Zend\Form\Form
     */
    public function buildForm(FieldsetProvider $provider, $year, $disabled = false)
    {
        $form = new Form;
        $inputFilter = new InputFilter();

        $this->addSubObservationFields($provider, $form);

        // Add the elements from the provider
        foreach ($provider->getElements($year) as $elementProvider) {
            $element = $this->getElement($elementProvider);

            if ($disabled) {
                $element['attributes']['disabled'] = 'disabled';
            }

            $element = $this->substituteVariables($element);

            $element = $this->addPriorYearValue($element, $elementProvider);

            $form->add($element);

            $inputFilter->add($elementProvider->getFormElementInputFilter());
        }

        $buttons = new Fieldset('buttons');
        $buttons->setAttribute('class', 'well well-small');
        $buttons->setLabel('');

        if (!$disabled) {
            // Add the save button
            $save = new Element\Submit('submit');
            $save->setValue('Save');
            $save->setAttribute('class', 'btn btn-primary');
            $save->setAttribute('id', 'save-button');
            $buttons->add($save);

            // Save and continue editing button
            $saveEdit = new Element\Submit('save-edit');
            $saveEdit->setValue('Save and Continue Editing');
            $saveEdit->setAttribute('class', 'btn btn-default');
            $saveEdit->setAttribute('id', 'save-edit-button');
            $buttons->add($saveEdit);

            $form->add($buttons);
        }

        //echo '<pre>'; print_r($inputFilter);
        $form->setInputFilter($inputFilter);


        return $form;
    }

    /**
     * @param FormElementProvider $elementProvider
     * @return \Zend\Form\Element
     */
    public function getElement(FormElementProvider $elementProvider)
    {
        return $elementProvider->getFormElement();
    }

    public function addSubObservationFields($provider, $form)
    {
        if ($provider->getUseSubObservation()) {
            $name = new Element\Text('name');
            $name->setLabel('Academic Division Name');
            $form->add($name);

            // Add the id, too
            $id = new Element\Hidden('id');
            $form->add($id);
        }
    }

    protected function substituteVariables($element)
    {
        if ($service = $this->getVariableSubstitutionService()) {
            $label = $service->substitute($element['options']['label']);
            $element['options']['label'] = $label;

            $desc = $service->substitute($element['options']['help-block']);
            $element['options']['help-block'] = $desc;
        }


        return $element;
    }

    protected function addPriorYearValue($element, Benchmark $benchmark)
    {
        if ($lastYearObservation = $this->getLastYearObservation()) {
            $dbColumn = $element['name'];

            if ($lastYearObservation->has($dbColumn)) {
                $value = $lastYearObservation->get($dbColumn);

                if (!is_null($value) && $value != '') {
                    $value = $benchmark->format($value);
                    $prior = '<span class="priorYearValue">Last year: ' . $value . '</span><br>';
                    $element['options']['help-block'] = $prior . $element['options']['help-block'];
                }
            }
        }

        return $element;
    }

    public function setVariableSubstitutionService($service)
    {
        $this->variableSubstitutionService = $service;

        return $this;
    }

    public function getVariableSubstitutionService()
    {
        return $this->variableSubstitutionService;
    }

    public function setLastYearObservation($obs)
    {
        $this->lastYearObservation = $obs;

        return $this;
    }

    public function getLastYearObservation()
    {
        return $this->lastYearObservation;
    }
}
