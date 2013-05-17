<?php

namespace Mrss\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Mvc\Controller\Plugin\FlashMessenger as FlashMessenger;

/**
 * @author Rick <rick@thewebmen.com>
 * @company The Webmen
 */
class FlashMessages extends AbstractHelper
{
    /**
     * @var FlashMessenger
     */
    protected $flashMessenger;

    public function setFlashMessenger(FlashMessenger $flashMessenger)
    {
        $this->flashMessenger = $flashMessenger;
    }

    public function __invoke($includeCurrentMessages = false)
    {
        $messages = array(
            FlashMessenger::NAMESPACE_ERROR => array(),
            FlashMessenger::NAMESPACE_SUCCESS => array(),
            FlashMessenger::NAMESPACE_INFO => array(),
            FlashMessenger::NAMESPACE_DEFAULT => array()
        );

        foreach ($messages as $ns => &$m) {
            $m = $this->flashMessenger->getMessagesFromNamespace($ns);
            if ($includeCurrentMessages) {
                $m = array_merge($m, $this->flashMessenger->getCurrentMessagesFromNamespace($ns));

                $this->flashMessenger->clearCurrentMessagesFromNamespace($ns);
            }
        }

        return $messages;
    }

    public function flashMessenger(
        $key = 'fm-bad',
        $template = '<span class="flashMessenger %s">%s</span>'
    ) {
        $flashMessenger = $this->_getFlashMessenger();

        //get messages from previous requests
        $messages = $flashMessenger->getMessages();

        //add any messages from this request
        if ($flashMessenger->hasCurrentMessages()) {
            $messages = array_merge(
                $messages,
                $flashMessenger->getCurrentMessages()
            );
            //we don't need to display them twice.
            $flashMessenger->clearCurrentMessages();
        }

        //initialise return string
        $output = '';

        //process messages
        foreach ($messages as $message) {
            if (is_array($message)) {
                list($key, $message) = each($message);
            }
            $output .= sprintf($template, $key, $message);
        }

        return $output;
    }
}
