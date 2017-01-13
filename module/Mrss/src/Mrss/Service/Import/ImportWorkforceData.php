<?php

namespace Mrss\Service\Import;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Guzzle\Tests\Service\Mock\Command\Sub\Sub;
use Mrss\Entity\College;
use Mrss\Entity\Observation;
use Mrss\Entity\Subscription;
use Mrss\Entity\User;

class ImportWorkforceData
{
    protected $wfDb;
    protected $serviceManager;
    protected $collegeAndUsersHandled = array();
    protected $studyId = 1;
    protected $sectionId = 2;
    protected $hydrator;

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

    protected function importSubscriptionToSection($wfSubscription)
    {
        $wfCollegeId = $wfSubscription['college_id'];

        $wfCollege = $this->getWfCollegeById($wfCollegeId);

        // Does the college exist? Find it by IPEDS id
        $ipeds = $wfCollege['ipeds'];
        $college = $this->getCollegeModel()->findOneByIpeds($ipeds);

        if (!$college) {
            $college = $this->createCollege($wfCollege, $wfSubscription);
        }

        if ($college) {
            $this->handleUsers($college, $wfCollege);
            $subscription = $this->handleSubscription($wfSubscription, $college);
            $this->handleData($subscription, $wfSubscription, $college);
        }



    }

    protected function createCollege($wfCollege, $wfSubscription)
    {
        echo 'Cannot locate ' . $wfCollege['name'] . '. Creating it.<br>';

        unset($wfCollege['id']);

        $college = new College();
        $college = $this->getHydrator()->hydrate($wfCollege, $college);

        $this->getCollegeModel()->save($college);

        $this->flush();

        pr($college->getName());

        echo 'College created from subscription ' . $wfSubscription['id'] . '<br>';

        return null;
    }

    protected function handleUsers($college, $wfCollege)
    {
        if (in_array($college->getId(), $this->collegeAndUsersHandled)) {
            return false;
        }

        $collegeId = $college->getId();

        // Do the users exist already?
        $users = $this->getWfUsers($collegeId);
        $created = false;
        foreach ($users as $wfUser) {
            $user = $this->getUserModel()->findOneByEmail($wfUser['email']);

            // Create user
            if (!$user) {
                echo $wfCollege['name'] . ' user ' . $wfUser['email'] . ' created.<br>';

                $user = new User();
                unset($wfUser['id']);

                $user = $this->getHydrator()->hydrate($wfUser, $user);
                $this->getUserModel()->save($user);
                $created = true;
            }
        }

        if ($created) {
            $this->flush();
        }

        $this->collegeAndUsersHandled[] = $college->getId();
    }

    protected function handleSubscription($wfSubscription, $college)
    {
        // Does a subscription exist for this college and year?
        $year = $wfSubscription['year'];

        $subscription = $this->getSubscriptionModel()->findOne($year, $college->getId(), $this->studyId);

        if (!$subscription) {
            echo " cannot find sub for $year, {$college->getId()}, $this->studyId<br>";
            echo 'Created subscription for ' . $college->getName() . ' ' . $year . '<br>';

            // Now add observation
            $observation = new Observation();
            $observation->setCollege($college);
            $observation->setYear($year);
            $observation->setMigrated(true);

            $this->getObservationModel()->save($observation);
            $this->flush();


            $subscription = new Subscription();
            unset($wfSubscription['id']);

            /** @var Subscription $subscription */
            $subscription = $this->getHydrator()->hydrate($wfSubscription, $subscription);
            $subscription->setCollege($college);
            $subscription->setStudy($this->getStudy());
            $subscription->setObservation($observation);
            $subscription->setBenchmarkModel($this->getBenchmarkModel());
            $subscription->setDatumModel($this->getDatumModel());

            $this->getSubscriptionModel()->save($subscription);



        }

        if ($subscription && !$subscription->hasSection($this->getSection())) {
            $subscription->addSection($this->getSection());

            echo 'Created section/module on existing subscription for '
                . $college->getName() . ' ' . $year . '<br>';
        }

        $this->flush();

        return $subscription;
    }

    /**
     * @param \Mrss\Entity\Subscription $subscription
     * @param $wfSubscription
     * @param $college
     */
    protected function handleData($subscription, $wfSubscription, $college)
    {
        $wfData = $this->getWfData($wfSubscription);
        //echo 'Here is where we import the data for ' . $college->getName() . ' ' . $wfSubscription['year'] . '<br>';

        //echo 'Data points found in WF: ' . count($wfData) . '<br>';

        foreach ($wfData as $wfDatum) {
            $dbColumn = $wfDatum['dbColumn'];
            $value = $wfDatum['floatValue'];
            if ($value === null && !empty($wfDatum['stringValue'])) {
                $value = $wfDatum['stringValue'];
            }

            if ($value) {
                $subscription->setValue($dbColumn, $value);
                //$subscription->getObservation()->set($dbColumn, $value);

                $this->getSubscriptionModel()->save($subscription);
                //echo "Value set for $dbColumn: $value<br>";
            }
        }

        $this->flush();

        //die('test');
    }

    protected function getWfData($wfSubscription)
    {
        $wfDb = $this->getWfDb();

        $sql = "SELECT * FROM data_values WHERE subscription_id = :subscriptionId";
        $statement = $wfDb->query($sql);

        $results = $statement->execute(array('subscriptionId' => $wfSubscription['id']));

        return $results;
    }

    protected function getWfCollegeById($collegeId)
    {
        $sql = "SELECT * FROM colleges WHERE id = :id";
        $statement = $this->getWfDb()->query($sql);

        $results = $statement->execute(array('id' => $collegeId));

        // Foreach subscription
        $row = $results->current();

        return $row;
    }

    protected function getWfUsers($collegeId)
    {
        $sql = "SELECT * FROM users WHERE college_id = :id";
        $statement = $this->getWfDb()->query($sql);

        $results = $statement->execute(array('id' => $collegeId));

        return $results;
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

    /**
     * @return \Mrss\Model\Subscription
     */
    protected function getSubscriptionModel()
    {
        return $this->getServiceManager()->get('model.subscription');
    }

    /**
     * @return \Mrss\Model\Observation
     */
    protected function getObservationModel()
    {
        return $this->getServiceManager()->get('model.observation');
    }

    /**
     * @return \Mrss\Model\User
     */
    protected function getUserModel()
    {
        return $this->getServiceManager()->get('model.user');
    }

    /**
     * @return \Mrss\Model\Section
     */
    protected function getSectionModel()
    {
        return $this->getServiceManager()->get('model.section');
    }

    /**
     * @return \Mrss\Model\Benchmark
     */
    protected function getBenchmarkModel()
    {
        return $this->getServiceManager()->get('model.benchmark');
    }

    /**
     * @return \Mrss\Model\Datum
     */
    protected function getDatumModel()
    {
        return $this->getServiceManager()->get('model.datum');
    }

    protected function getSection()
    {
        return $this->getSectionModel()->find($this->sectionId);
    }

    protected function getStudy()
    {
        return $this->getServiceManager()->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
    }

    protected function getHydrator()
    {
        if (!$this->hydrator) {
            $entityManager = $this->getServiceManager()->get('em');
            $this->hydrator = new DoctrineHydrator($entityManager);
        }

        return $this->hydrator;
    }

    protected function flush()
    {
        $this->getCollegeModel()->getEntityManager()->flush();
    }
}
