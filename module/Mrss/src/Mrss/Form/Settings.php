<?php

namespace Mrss\Form;

use Mrss\Form\AbstractForm;

class Settings extends AbstractForm
{
    public function __construct()
    {
        // Call the parent constructor
        parent::__construct('settings');

        $this->add($this->getButtonFieldset());
    }
}
