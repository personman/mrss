<?php

namespace Mrss\Service\Import;

class ImportWorkforceData
{
    protected $wfDb;
    protected $serviceManager;

    public function import()
    {
        $wfDb = $this->getWfDb();

        $sql = "SELECT * FROM subscriptions";
        $statement = $wfDb->query($sql);

        $results = $statement->execute();

        // Foreach subscription
        foreach ($results as $row) {
            $this->importSubscriptionToSection($row);

            echo '<hr>';

        }



        pr(get_class($wfDb));
    }

    protected function importSubscriptionToSection($subscription)
    {
        //pr($subscription);
        $year = $subscription['year'];
        $wfCollegeId = $subscription['college_id'];

        $wfCollege = $this->getWfCollegeById($wfCollegeId);
        //pr($wfCollege);

        // Does the college exist? Find it by IPEDS id
        $ipeds = $wfCollege['ipeds'];
        $college = $this->getCollegeModel()->findOneByIpeds($ipeds);

        if ($college) {
            //pr($college->getName());
        } else {
            echo 'Cannot locate ' . $wfCollege['name'] . '<br>';
        }

    }

    protected function getWfCollegeById($id)
    {
        $sql = "SELECT * FROM colleges WHERE id = :id";
        $statement = $this->getWfDb()->query($sql);

        $results = $statement->execute(array('id' => $id));

        // Foreach subscription
        $row = $results->current();

        return $row;
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function setWfDb($wfDb)
    {
        $this->wfDb = $wfDb;

        return $this;
    }

    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getWfDb()
    {
        return $this->wfDb;
    }

    /**
     * @return \Mrss\Model\College
     */
    protected function getCollegeModel()
    {
        return $this->getServiceManager()->get('model.college');
    }
}
