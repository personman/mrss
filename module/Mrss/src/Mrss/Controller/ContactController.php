<?php

namespace Mrss\Controller;

use PhlyContact\Controller\ContactController as PhlyController;

class ContactController extends PhlyController
{

    public function indexAction()
    {
        $subject = $this->params()->fromRoute('subject');
        if ($subject) {
            $this->form->get('subject')->setValue($subject);
        }

        $body = $this->params()->fromRoute('body');
        if ($body) {
            $this->form->get('body')->setValue($body);
        }

        return array(
            'form' => $this->form,
        );
    }
    /**
     * Override this function to add MRSS info to the email
     *
     * @param array $data
     */
    protected function sendEmail(array $data)
    {
        $currentStudy = $this->currentStudy();
        $study = $currentStudy->getName();

        $from    = $data['from'];
        $subject = "[Contact Form: $study] " . $data['subject'];
        $body    = "From: $from\n\n" . $data['body'];

        $this->message->addFrom($from)
            ->addReplyTo($from)
            ->setSubject($subject)
            ->setBody($body);
        $this->transport->send($this->message);
    }
}
