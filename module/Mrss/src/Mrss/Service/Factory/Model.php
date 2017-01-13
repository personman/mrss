<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Factory
 *
 * Model factory.
 *
 * @package Mrss\Service\Report
 */
class Model implements AbstractFactoryInterface
{
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $map = $this->getClassMap();
        $class = $map[$name];

        $model = new $class();

        $em = $serviceLocator->get('em');

        $model->setEntityManager($em);

        return $model;
    }

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $map = $this->getClassMap();

        return (!empty($map[$name]));
    }

    /**
     * Don't use capital letters in the keys of this array.
     *
     * @return array
     */
    protected function getClassMap()
    {
        return array(
            'model.user' => 'Mrss\Model\User',
            'model.college' => 'Mrss\Model\College',
            'model.system' => 'Mrss\Model\System',
            'model.observation' => 'Mrss\Model\Observation',
            'model.subobservation' => 'Mrss\Model\SubObservation',
            'model.benchmark' => 'Mrss\Model\Benchmark',
            'model.benchmark.heading' => 'Mrss\Model\BenchmarkHeading',
            'model.benchmark.group' => 'Mrss\Model\BenchmarkGroup',
            'model.criterion' => 'Mrss\Model\Criterion',
            'model.study' => 'Mrss\Model\Study',
            'model.section' => 'Mrss\Model\Section',
            'model.subscription' => 'Mrss\Model\Subscription',
            'model.subscription.draft' => 'Mrss\Model\SubscriptionDraft',
            'model.setting' => 'Mrss\Model\Setting',
            'model.report' => 'Mrss\Model\Report',
            'model.report.item' => 'Mrss\Model\ReportItem',
            'model.page' => 'Mrss\Model\Page',
            'model.payment' => 'Mrss\Model\Payment',
            'model.chart' => 'Mrss\Model\Chart',
            'model.issue' => 'Mrss\Model\Issue',
            'model.ipeds.institution' => 'Mrss\Model\IpedsInstitution',
            'model.change.set' => 'Mrss\Model\ChangeSet',
            'model.offer.code' => 'Mrss\Model\OfferCode',
            'model.peer.group' => 'Mrss\Model\PeerGroup',
            'model.peer.benchmark' => 'Mrss\Model\PeerBenchmark',
            'model.percentile' => 'Mrss\Model\Percentile',
            'model.percentile.rank' => 'Mrss\Model\PercentileRank',
            'model.outlier' => 'Mrss\Model\Outlier',
            'model.suppression' => 'Mrss\Model\Suppression',
            'model.percentchange' => 'Mrss\Model\PercentChange',
            'model.datum' => 'Mrss\Model\Datum',
        );
    }
}
