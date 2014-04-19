<?php

namespace Mrss\Service;

use Mrss\Entity\Benchmark;

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
    }
}
