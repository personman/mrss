<?php

namespace Mrss\Service\Report\Max;

use Mrss\Service\Report\Max;
use Mrss\Entity\Observation;

class National extends Max
{
    public function getData(Observation $observation)
    {
        $this->setObservation($observation);

        $reportData = array();

        $reportData['instructional'] = $this->getInstructionalActivityCosts();
        $reportData['studentServices'] = $this->getStudentServicesCosts();
        $reportData['studentServicesPercentages'] = $this->getStudentServicesPercentages();
        $reportData['academicSupport'] = $this->getAcademicSupport();
        $reportData['studentSuccess'] = $this->getStudentSuccess();

        //pr($reportData);
        return $reportData;
    }

    public function getInstructionalActivityCosts()
    {
        /** @var ActivityReport\Instructional $report */
        $report = $this->getServiceManager()->get('service.report.max.activity.instructional');
        return $report->getData($this->getObservation());
    }

    public function getStudentServicesCosts()
    {
        /** @var ActivityReport\StudentServices $report */
        $report = $this->getServiceManager()->get('service.report.max.activity.ss');
        return $report->getData($this->getObservation());
    }

    public function getStudentServicesPercentages()
    {
        /** @var ActivityReport\StudentServices $report */
        $report = $this->getServiceManager()->get('service.report.max.activity.ss.perc');
        return $report->getData($this->getObservation());
    }

    public function getAcademicSupport()
    {
        /** @var ActivityReport\StudentServices $report */
        $report = $this->getServiceManager()->get('service.report.max.activity.as');
        return $report->getData($this->getObservation());
    }

    public function getStudentSuccess()
    {
        /** @var \Mrss\Service\Report\National $report */
        $report = $this->getServiceManager()->get('service.report.national');
        $data = $report->getData($this->getObservation(), false, 42);
        return $data[0]['benchmarks'];
    }

    protected function getSystem()
    {
        return null;
    }
}
