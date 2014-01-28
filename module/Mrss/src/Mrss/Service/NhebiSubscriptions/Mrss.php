<?php

namespace Mrss\Service\NhebiSubscriptions;

class Mrss
{
    protected $subscriptionModel;
    protected $collegeModel;

    protected $studyId;

    public function setSubscriptionModel($model)
    {
        $this->subscriptionModel = $model;

        return $this;
    }

    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
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

    public function setStudyId($id)
    {
        $this->studyId = $id;

        return $this;
    }

    public function getStudyId()
    {
        return $this->studyId;
    }

    public function checkSubscription($year, $ipeds)
    {
        $subscriptionExists = false;

        // Look up the college first
        $college = $this->getCollegeModel()->findOneByIpeds($ipeds);

        // If the college exists, check for the subscription
        if (!empty($college)) {
            $collegeId = $college->getId();

            $subscription = $this->getSubscriptionModel()
                ->findOne($year, $collegeId, $this->getStudyId());

            if (!empty($subscription)) {
                $subscriptionExists = true;
            }
        }

        return $subscriptionExists;
    }
}
