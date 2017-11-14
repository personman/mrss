<?php

namespace Mrss\Service;

use Mrss\Entity\Study;
use Zend\Log\Formatter\Simple;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\ServiceManager\ServiceManager;
use Mrss\Service\Report\Calculator;
use Zend\Mail\Transport\Smtp;

class ReportBase
{
    /**
     * @var Study
     */
    protected $study;

    protected $studyConfig;

    /**
     * @var array
     */
    protected $subscriptions = array();

    /**
     * @var Calculator
     */
    protected $calculator;

    protected $serviceManager;

    /**
     * @var ComputedFields
     */
    protected $computedService;

    /**
     * @var VariableSubstitution
     */
    protected $variableSubstitution;

    /**
     * @var \Mrss\Model\Subscription
     */
    protected $subscriptionModel;

    /**
     * @var \Mrss\Entity\Subscription
     */
    protected $subscription;

    /**
     * @var \Mrss\Model\Benchmark
     */
    protected $benchmarkModel;

    /**
     * @var \Mrss\Model\College
     */
    protected $collegeModel;

    /**
     * @var \Mrss\Model\Percentile
     */
    protected $percentileModel;

    /**
     * @var \Mrss\Model\PercentileRank
     */
    protected $percentileRankModel;

    /**
     * @var \Mrss\Model\Setting
     */
    protected $settingModel;

    /**
     * @var \Mrss\Model\Outlier
     */
    protected $outlierModel;

    /**
     * @var \Mrss\Model\System
     */
    protected $systemModel;

    /**
     * @var \Mrss\Model\Issue
     */
    protected $issueModel;

    /**
     * @var \Mrss\Model\PercentChange
     */
    protected $percentChangeModel;

    /**
     * @var \Mrss\Entity\Observation
     */
    protected $observation;

    /**
     * @var \Mrss\Model\Observation
     */
    protected $observationModel;

    /**
     * @var Smtp
     */
    protected $mailTransport;

    protected $debug = false;

    protected $start;

    protected $system;

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
    }

    public function setComputedService(ComputedFields $service)
    {
        $this->computedService = $service;

        return $this;
    }

    /**
     * @return ComputedFields
     */
    public function getComputedService()
    {
        return $this->computedService;
    }

    /**
     * @param $serviceManager
     * @return $this
     */
    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setSubscriptionModel($model)
    {
        $this->subscriptionModel = $model;

        return $this;
    }

    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
    }

    public function setBenchmarkModel($model)
    {
        $this->benchmarkModel = $model;

        return $this;
    }

    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }

    public function setCollegeModel($model)
    {
        $this->collegeModel = $model;

        return $this;
    }

    public function getCollegeModel()
    {
        return $this->collegeModel;
    }

    public function setPercentileModel($model)
    {
        $this->percentileModel = $model;

        return $this;
    }

    public function getPercentileModel()
    {
        return $this->percentileModel;
    }

    public function setPercentileRankModel($model)
    {
        $this->percentileRankModel = $model;

        return $this;
    }

    /**
     * @return \Mrss\Model\PercentileRank
     */
    public function getPercentileRankModel()
    {
        return $this->percentileRankModel;
    }

    public function setSettingModel($model)
    {
        $this->settingModel = $model;

        return $this;
    }

    public function getSettingModel()
    {
        return $this->settingModel;
    }

    public function setOutlierModel($model)
    {
        $this->outlierModel = $model;

        return $this;
    }

    public function getOutlierModel()
    {
        return $this->outlierModel;
    }

    public function setSystemModel($model)
    {
        $this->systemModel = $model;

        return $this;
    }

    /**
     * @return \Mrss\Model\System
     */
    public function getSystemModel()
    {
        return $this->systemModel;
    }

    public function setIssueModel($model)
    {
        $this->issueModel = $model;

        return $this;
    }

    public function getIssueModel()
    {
        return $this->issueModel;
    }

    public function setPercentChangeModel($model)
    {
        $this->percentChangeModel = $model;

        return $this;
    }

    /**
     * @return \Mrss\Model\PercentChange
     */
    public function getPercentChangeModel()
    {
        return $this->percentChangeModel;
    }


    public function setCalculator(Calculator $calculator)
    {
        $this->calculator = $calculator;

        return $this;
    }

    public function getCalculator()
    {
        return $this->calculator;
    }

    public function setVariableSubstitution(VariableSubstitution $service)
    {
        $this->variableSubstitution = $service;

        return $this;
    }

    /**
     * @return VariableSubstitution
     */
    public function getVariableSubstitution()
    {
        return $this->variableSubstitution;
    }

    public function setMailTransport(Smtp $transport)
    {
        $this->mailTransport = $transport;

        return $this;
    }

    public function getMailTransport()
    {
        return $this->mailTransport;
    }

    protected function debug($variable)
    {
        if ($this->debug) {
            pr($variable);
        }
    }

    protected function debugTimer($message = null)
    {
        if ($this->debug) {
            $elapsed = round(microtime(1) - $this->start, 3);
            $message = $elapsed . "s: " . $message;
            $this->debug($message);
        }
    }

    /**
     * @return \Mrss\Model\PeerGroup
     */
    protected function getPeerGroupModel()
    {
        return $this->getServiceManager()->get('model.peer.group');
    }

    public function setSystem($system)
    {
        $this->system = $system;

        return $this;
    }

    /**
     * @return \Mrss\Entity\System
     */
    public function getSystem()
    {
        return $this->system;
    }


    public function setStudyConfig($config)
    {
        $this->studyConfig = $config;

        return $this;
    }

    public function getStudyConfig()
    {
        return $this->studyConfig;
    }

    /**
     * @param bool $shortFormat
     * @return Logger
     */
    protected function getErrorLog($shortFormat = false)
    {
        $formatter = new Simple('%message%' . PHP_EOL);

        $writer = new Stream('error.log');

        if ($shortFormat) {
            $writer->setFormatter($formatter);
        }

        $logger = new Logger;
        $logger->addWriter($writer);

        return $logger;
    }
}
