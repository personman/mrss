<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\ObservationGenerator as OG;

class ObservationGenerator implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $generator = new OG();

        $currentStudy = $sm->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $generator->setStudy($currentStudy);

        return $generator;
    }
}
