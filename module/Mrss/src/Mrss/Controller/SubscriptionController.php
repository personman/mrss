<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\Subscription as SubscriptionForm;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class SubscriptionController extends AbstractActionController
{
    public function addAction()
    {
        $form = new SubscriptionForm;
        $message = null;

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                var_dump($form->getData());
                die('is valid');
            } else {
                $message = "Please correct the problems below.";
            }
        }

        return array(
            'form' => $form,
            'message' => $message
        );
    }
}
