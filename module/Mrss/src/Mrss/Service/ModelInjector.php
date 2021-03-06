<?php

namespace Mrss\Service;

use Mrss\Entity\Benchmark;
use Mrss\Entity\Subscription;
use Mrss\Entity\Structure;

class ModelInjector
{
    protected $serviceLocator;

    public function __construct($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function postLoad($event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof Benchmark) {
            $entity->setBenchmarkModel(
                $this->serviceLocator->get('model.benchmark')
            );
        }

        if ($entity instanceof Structure) {
            $entity->setBenchmarkModel(
                $this->serviceLocator->get('model.benchmark')
            );
        }

        if ($entity instanceof Subscription) {
            $entity->setBenchmarkModel(
                $this->serviceLocator->get('model.benchmark')
            );
            $entity->setDatumModel(
                $this->serviceLocator->get('model.datum')
            );
            $entity->setStudyConfig(
                $this->serviceLocator->get('study')
            );
        }
    }
}
