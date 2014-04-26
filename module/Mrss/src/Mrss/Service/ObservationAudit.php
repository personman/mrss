<?php

namespace Mrss\Service;

use Mrss\Entity\Change;
use Mrss\Entity\Study;
use Mrss\Entity\Observation;
use Mrss\Entity\User;
use Mrss\Entity\ChangeSet;
use Mrss\Model\ChangeSet as ChangeSetModel;
use Mrss\Model\Benchmark as BenchmarkModel;

class ObservationAudit
{
    /**
     * @var ChangeSetModel
     */
    protected $changeSetModel;

    /**
     * @var BenchmarkModel
     */
    protected $benchmarkModel;

    /**
     * @var Study
     */
    protected $study;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var User
     */
    protected $impersonator;

    /**
     * Compare two observations and log any changes to the database
     *
     * @param Observation $old
     * @param Observation $new
     * @param string $editType
     * @internal param \Mrss\Entity\User|null $impersonator
     * @return ChangeSet|null
     */
    public function logChanges(
        Observation $old,
        Observation $new,
        $editType
    ) {
        // Were there any changes?
        $changes = $this->compare($old, $new);

        $changeSet = null;
        if (!empty($changes)) {
            // Create the changeSet
            $changeSet = new ChangeSet;
            $changeSet->setUser($this->getUser());
            $changeSet->setDate(new \DateTime('now'));
            $changeSet->setObservation($new);
            $changeSet->setStudy($this->getStudy());
            $changeSet->setEditType($editType);

            if ($impersonator = $this->getImpersonator()) {
                $em = $this->getBenchmarkModel()->getEntityManager();
                $impersonatorRef = $em
                    ->getReference('\Mrss\Entity\User', $impersonator->getId());
                $changeSet->setImpersonatingUser($impersonatorRef);
            }

            // Create the change entities
            $changeEntities = array();
            foreach ($changes as $dbColumn => $change) {
                $changeEntity = $this->getChangeEntity($dbColumn, $change);
                if ($changeEntity) {
                    $changeEntity->setChangeSet($changeSet);
                    $changeEntities[] = $changeEntity;
                }
            }

            $changeSet->setChanges($changeEntities);

            $this->getChangeSetModel()->save($changeSet);
        }

        return $changeSet;
    }

    public function getChangeEntity($dbColumn, $change)
    {
        $changeEntity = null;

        // Find the benchmark
        $benchmark = $this->getBenchmarkModel()->findOneByDbColumn($dbColumn);

        if (!empty($benchmark)) {
            $changeEntity = new Change();
            $changeEntity->setBenchmark($benchmark);
            $changeEntity->setOldValue($change['old']);
            $changeEntity->setNewValue($change['new']);
        }

        return $changeEntity;
    }

    public function compare(Observation $old, Observation $new)
    {
        $changes = array();

        $benchmarks = $old->getAllBenchmarks();
        foreach ($benchmarks as $benchmark) {
            $oldValue = $old->get($benchmark);
            $newValue = $new->get($benchmark);

            if ($oldValue != $newValue) {
                $changes[$benchmark] = array(
                    'old' => $oldValue,
                    'new' => $newValue
                );
            }
        }

        return $changes;
    }

    public function setChangeSetModel(ChangeSetModel $model)
    {
        $this->changeSetModel = $model;

        return $this;
    }

    public function getChangeSetModel()
    {
        return $this->changeSetModel;
    }

    public function setBenchmarkModel(BenchmarkModel $model)
    {
        $this->benchmarkModel = $model;

        return $this;
    }

    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }

    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setImpersonator(User $user)
    {
        $this->impersonator = $user;

        return $this;
    }

    public function getImpersonator()
    {
        return $this->impersonator;
    }

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
    }

}
