<?php

namespace Mrss\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Show a form element with errors, but no label or description
 */
class SimpleFormElement extends AbstractHelper
{
    public function __invoke($element)
    {
        $formLabel = $this->getView()->plugin('formLabel');

        $extraClass = '';
        if ($element->getMessages()) {
            $extraClass = 'error';
        }

        $html =  "<div class='control-group $extraClass'>";

        $html .= $formLabel->openTag();
        $html .= $this->getView()->formInput($element);
        $html .= $this->getView()->formElementErrors($element);
        $html .= $formLabel->closeTag();
        $html .= "</div>\n";

        return $html;
    }
}
