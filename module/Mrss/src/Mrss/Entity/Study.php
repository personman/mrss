<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Study/project
 *
 * Groups of benchmarkGroups
 *
 * @ORM\Entity
 * @ORM\Table(name="studies")
 */
class Study
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="BenchmarkGroup", mappedBy="study")
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    protected $benchmarkGroups;

    public function __construct()
    {
        $this->benchmarkGroups = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setBenchmarkGroups($benchmarkGroups)
    {
        $this->benchmarkGroups = $benchmarkGroups;

        return $this;
    }

    public function getBenchmarkGroups()
    {
        return $this->benchmarkGroups;
    }

    public function getCompletionPercentage(Observation $observation)
    {
        $total = 0;
        $completed = 0;

        // Loop over each benchmarkGroup and sum up the counts
        foreach ($this->getBenchmarkGroups() as $benchmarkGroup) {
            $total += count($benchmarkGroup->getBenchmarks());
            $completed += $benchmarkGroup->countCompleteFieldsInObservation($observation);
        }

        $percentage = ($completed / $total * 100);
        $percentage = round($percentage, 1);

        return $percentage;
    }
}
