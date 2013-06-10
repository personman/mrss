<?php

namespace Mrss\Entity;

interface FormElementProviderInterface
{
    public function getFormElement();

    public function getFormElementInputFilter();
}
