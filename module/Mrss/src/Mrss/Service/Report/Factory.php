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

        $currentCollege = $sm->get('ControllerPluginManager')
            ->get('currentCollege')->getCurrentCollege();

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
        $report->setVariableSubstitution($sm->get('service.variableSubstitution'));
        $report->setCollege($currentCollege);

        // So the report can create other reports:
        $report->setServiceManager($sm);

        $report = $this->addExtraDependencies($report, $name, $sm);

        return $report;
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
            'service.report.national' => 'Mrss\Service\Report\National',
            'service.report.percentile' => 'Mrss\Service\Report\Percentile',
            'service.report.outliers' => 'Mrss\Service\Report\Outliers',
            'service.report.executive' => 'Mrss\Service\Report\Executive',
            'service.report.peer' => 'Mrss\Service\Report\Peer',
            'service.report.performers' => 'Mrss\Service\Report\BestPerformers',
            'service.report.max.internal' => 'Mrss\Service\Report\Max\Internal',
            'service.report.max.national' => 'Mrss\Service\Report\Max\National',
            'service.report.builder' => 'Mrss\Service\Report\CustomReportBuilder',
            'builder.bubble' =>
                'Mrss\Service\Report\ChartBuilder\BubbleBuilder',
            'builder.line' =>
                'Mrss\Service\Report\ChartBuilder\LineBuilder',
            'service.report.max.activity.instructional' =>
                'Mrss\Service\Report\Max\ActivityReport\Instructional',
            'service.report.max.activity.ss' =>
                'Mrss\Service\Report\Max\ActivityReport\StudentServices',
            'service.report.max.activity.ss.perc' =>
                'Mrss\Service\Report\Max\ActivityReport\StudentServicesPercentages',
            'service.report.max.activity.as' =>
                'Mrss\Service\Report\Max\ActivityReport\AcademicSupport',
            'service.report' => 'Mrss\Service\Report',
        );
    }

    protected function addExtraDependencies($report, $name, $sm)
    {
        if ($name == 'service.report.peer') {
            $report->setPeerBenchmarkModel($sm->get('model.peerBenchmark'));
        }

        return $report;
    }
}
