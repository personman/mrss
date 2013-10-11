<?php

namespace Mrss\Controller;

use PhlyContact\Controller\ContactController as PhlyController;

class ContactController extends PhlyController
{
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
