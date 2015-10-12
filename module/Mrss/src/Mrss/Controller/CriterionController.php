<?php

namespace Mrss\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Mrss\Form\Criterion;
use Zend\Mvc\Controller\AbstractActionController;

class CriterionController extends AbstractActionController
{
    public function indexAction()
    {

    }

    public function addAction()
    {
        $benchmarks = $this->currentStudy()->getStructuredBenchmarks(false, 'id');

        $form = new Criterion($benchmarks);

        return array(
            'form' => $form
        );
    }
}
