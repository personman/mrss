<?php

namespace Mrss\Service\Report;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Factory
 *
 * Report factory. Creates instances of the classes that descend from Report and gives them
 * their dependencies.
 *
 * @package Mrss\Service\Report
 */
class Factory implements AbstractFactoryInterface
{
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $map = $this->getClassMap();
        $class = $map[$name];

        /** @var \Mrss\Service\Report $report */
        $report = new $class(null);
        
        // Pass in the dependencies
        $sm = $serviceLocator;
        $currentStudy = $sm->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();

        $report->setSubscriptionModel($sm->get('model.subscription'));
        $report->setStudy($currentStudy);
        $report->setCalculator($sm->get('service.report.calculator'));
        $report->setPercentileModel($sm->get('model.percentile'));
        $report->setPercentileRankModel(
            $sm->get('model.percentileRank')
        );
        $report->setBenchmarkModel($sm->get('model.benchmark'));
        $report->setCollegeModel($sm->get('model.college'));
        $report->setSettingModel($sm->get('model.setting'));
        $report->setOutlierModel($sm->get('model.outlier'));
        $report->setSystemModel($sm->get('model.system'));
        $report->setComputedFieldsService($sm->get('computedFields'));
        $report->setMailTransport($sm->get('mail.transport'));
        $report->setVariableSubstition($sm->get('service.variableSubstitution'));

        return $report;
    }

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $map = $this->getClassMap();
        //pr($name);

        return (!empty($map[$name]));
    }

    protected function getClassMap()
    {
        return array(
            'service.report.national' => 'Mrss\Service\Report\National',
            'service.report.percentile' => 'Mrss\Service\Report\Percentile',
            'service.report.outliers' => 'Mrss\Service\Report\Outliers',
            'service.report.executive' => 'Mrss\Service\Report\Executive',
            'service.report.peer' => 'Mrss\Service\Report\Peer',
            'service.report.performers' => 'Mrss\Service\Report\BestPerformers',
        );
    }
}
