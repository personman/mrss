<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\ImportBenchmarks as IB;

class ImportBenchmarks implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $importer = new IB();

        // Models
        $benchmarkModel = $sm->get('model.benchmark');
        $importer->setBenchmarkModel($benchmarkModel);

        $benchmarkGroupModel = $sm->get('model.benchmark.group');
        $importer->setBenchmarkGroupModel($benchmarkGroupModel);

        $computedFieldsService = $sm->get('computedFields');
        $importer->setComputedFieldsService($computedFieldsService);

        $importer->setEntityManager($sm->get('em'));

        return $importer;
    }
}
