<?php

namespace Mrss\Service\Export;

use Mrss\Service\Export;
use Mrss\Entity\User as UserEntity;
use PHPExcel;

class Lapsed extends Export
{
    protected $yearsToGoBack = 2;

    protected $study;

    protected $subscriptionModel;

    public function export()
    {
        $colleges = $this->getLapsedColleges();
        die('ok');
    }

    protected function getLapsedColleges()
    {
        $priorColleges = $this->getPriorYearColleges();
        $currentColleges = $this->getCurrentColleges();

        $lapsedCollegeIds = array_diff($priorColleges, $currentColleges);

        pr(count($lapsedCollegeIds));
        pr($lapsedCollegeIds);
    }

    protected function getPriorYearColleges()
    {
        $currentYear = $this->getStudy()->getCurrentYear();
        $yearsToCheck = array();
        foreach (range(1, $this->yearsToGoBack) as $difference) {
            $yearsToCheck[] = $currentYear - $difference;
        }

        $priorYearColleges = array();
        foreach ($yearsToCheck as $year) {
            $collegeIds = $this->getCollegeIdsSubscribedForYear($year);
            $priorYearColleges = array_merge($priorYearColleges, $collegeIds);
        }

        // Remove duplicates
        $priorYearColleges = array_unique($priorYearColleges);

        return $priorYearColleges;
    }

    protected function getCollegeIdsSubscribedForYear($year)
    {
        $subscriptions = $this->getSubscriptionModel()->findByStudyAndYear($this->getStudy()->getId(), $year);

        $collegeIds = array();
        foreach ($subscriptions as $subscription) {
            $collegeIds[] = $subscription->getCollege()->getId();
        }

        return $collegeIds;
    }

    protected function getCurrentColleges()
    {
        $currentYear = $this->getStudy()->getCurrentYear();

        return $this->getCollegeIdsSubscribedForYear($currentYear);
    }

    /**
     * @return \Mrss\Entity\Study
     */
    public function getStudy()
    {
        return $this->study;
    }

    /**
     * @param \Mrss\Entity\Study $study
     * @return Lapsed
     */
    public function setStudy($study)
    {
        $this->study = $study;
        return $this;
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
    }

    /**
     * @param \Mrss\Model\Subscription $subscriptionModel
     * @return Lapsed
     */
    public function setSubscriptionModel($subscriptionModel)
    {
        $this->subscriptionModel = $subscriptionModel;
        return $this;
    }


}
