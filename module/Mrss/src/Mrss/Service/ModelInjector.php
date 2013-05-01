<?php

namespace Mrss\Service;

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

        if ($entity instanceof \Mrss\Entity\Benchmark) {
            $entity->setBenchmarkModel(
                $this->serviceLocator->get('model.benchmark')
            );
        }
    }
}
