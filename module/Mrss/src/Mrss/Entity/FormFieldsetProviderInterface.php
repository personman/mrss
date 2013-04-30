<?php

namespace Mrss\Entity;

interface FormFieldsetProviderInterface
{
    public function getElements($year);

    public function getLabel();
}
