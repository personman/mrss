<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\ImportNccwtp as Importer;

class ImportNccwtp implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $nccwtp = new Importer();

        return $nccwtp;
    }
}
