<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Validator\Equation;

class EquationValidator implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $validator = new Equation(
            $sm->get('computedFields'),
            $sm->get('model.benchmark')
        );

        return $validator;
    }
}
