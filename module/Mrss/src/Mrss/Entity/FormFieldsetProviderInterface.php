<?php

namespace Mrss\Entity;

interface FormFieldsetProviderInterface
{
    public function getElements();

    public function getLabel();
}
