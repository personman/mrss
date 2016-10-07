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

    // Keyed by id
    protected $allColleges = array();

    public function export()
    {
        $filename = 'lapsed-institutions.xlsx';
        $startingCell = 'A1';

        $colleges = $this->getLapsedColleges();

        $exportData = array();

        // Add the header
        $exportData[] = array('Institution', 'Name', 'Phone', 'Email', 'Memberships');

        // Loop over the colleges
        foreach ($colleges as $college) {

            $firstUser = true;
            foreach ($college->getDataUsers() as $user) {

                $collegeName = $college->getName();
                $yearsSubscribed = implode(', ', $college->getYearsWithSubscriptions($this->getStudy(), false));
                if (!$firstUser) {
                    $collegeName = null;
                    $yearsSubscribed = null;
                }

                $exportData[] = array(
                    $collegeName,
                    $user->getFullName(),
                    $user->getFullPhone(),
                    $user->getEmail(),
                    $yearsSubscribed
                );

                $firstUser = false;
            }
        }


        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();

        $sheet->fromArray($exportData, null, $startingCell);

        foreach (range(0, 5) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        // redirect output to client browser
        $this->downloadExcel($excel, $filename);
    }

    /**
     * @return \Mrss\Entity\College[]
     */
    protected function getLapsedColleges()
    {
        $priorColleges = $this->getPriorYearColleges();
        $currentColleges = $this->getCurrentColleges();

        $lapsedCollegeIds = array_diff($priorColleges, $currentColleges);

        $lapsedColleges = array();
        foreach ($lapsedCollegeIds as $id) {
            $lapsedColleges[] = $this->allColleges[$id];
        }

        return $lapsedColleges;
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
            $this->allColleges[$subscription->getCollege()->getId()] = $subscription->getCollege();
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
