<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity
 * @ORM\Table(name="suppressions")
 */
class Suppression
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="BenchmarkGroup")
     */
    protected $benchmarkGroup;

    /**
     * @ORM\ManyToOne(targetEntity="Subscription", inversedBy="suppressions")
     */
    protected $subscription;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return BenchmarkGroup
     */
    public function getBenchmarkGroup()
    {
        return $this->benchmarkGroup;
    }

    /**
     * @param mixed $benchmarkGroup
     * @return $this
     */
    public function setBenchmarkGroup(BenchmarkGroup $benchmarkGroup)
    {
        $this->benchmarkGroup = $benchmarkGroup;

        return $this;
    }

    public function setSubscription(Subscription $subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function getSubscription()
    {
        return $this->subscription;
    }
}
