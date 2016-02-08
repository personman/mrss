<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\ExportNccbp as Exporter;

class ExportNccbp implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $nccbpDb = $sm->get('nccbp-db');
        $exporter = new Exporter($nccbpDb);

        return $exporter;
    }
}
