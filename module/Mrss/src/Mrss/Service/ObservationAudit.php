<?php

namespace Mrss\Service;

use Mrss\Entity\Change;
use Mrss\Entity\Observation;
use Mrss\Entity\User;
use Mrss\Entity\ChangeSet;
use Mrss\Model\ChangeSet as ChangeSetModel;

class ObservationAudit
{
    /**
     * @var ChangeSetModel
     */
    protected $changeSetModel;

    /**
     * @param Observation $old
     * @param Observation $new
     * @param User $user
     * @param User|null $impersonator
     * @return ChangeSet|null
     */
    public function logChanges(
        Observation $old,
        Observation $new,
        User $user,
        $impersonator = null
    ) {
        $changes = $this->compare($old, $new);

        $changeSet = null;
        if (!empty($changes)) {
            $changeSet = new ChangeSet;
            $changeSet->setUser($user);
            $changeSet->setDate(new \DateTime('now'));

            if (!empty($impersonator)) {
                $changeSet->setImpersonatingUser($impersonator);
            }

            // Create the change entities
            $changeEntities = array();
            foreach ($changes as $dbColumn => $change) {
                if ($changeEntity = $this->getChangeEntity($dbColumn, $change)) {
                    $changeEntity->setChangeSet($changeSet);
                    $changeEntities[] = $changeEntity;
                }
            }

            $changeSet->setChanges($changeEntities);
        }

        return $changeSet;
    }

    public function getChangeEntity($dbColumn, $change)
    {
        $changeEntity = new Change();

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
}
