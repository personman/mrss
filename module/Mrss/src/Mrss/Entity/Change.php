<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mrss\Entity\ChangeSet;

/**
 * Entity to track Observation changes
 *
 * @ORM\Entity
 * @ORM\Table(name="changes")
 */
class Change {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ChangeSet", inversedBy="changes")
     * @var ChangeSet
     */
    protected $changeSet;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setChangeSet(ChangeSet $changeSet)
    {
        $this->changeSet = $changeSet;

        return $this;
    }

    public function getChangeSet()
    {
        return $this->changeSet;
    }
}
