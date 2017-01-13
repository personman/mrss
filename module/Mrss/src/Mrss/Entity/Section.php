<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Study section/module
 *
 * Groups of benchmarkGroups
 *
 * @ORM\Entity
 * @ORM\Table(name="study_sections")
 */
class Section
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
     * @ORM\ManyToMany(targetEntity="BenchmarkGroup", inversedBy="sections")
     * @ORM\JoinTable(name="sections_benchmark_groups")
     * @var \Mrss\Entity\BenchmarkGroup[]
     */
    protected $benchmarkGroups;

    /**
     * @ORM\Column(type="float")
     */
    protected $price;

    /**
     * @ORM\Column(type="float")
     */
    protected $comboPrice;

    /**
     * @ORM\ManyToOne(targetEntity="Study", inversedBy="sections")
     */
    protected $study;



    public function __construct()
    {
        $this->benchmarkGroups = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Section
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Section
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return Section
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return BenchmarkGroup[]
     */
    public function getBenchmarkGroups()
    {
        return $this->benchmarkGroups;
    }

    /**
     * @param BenchmarkGroup[] $benchmarkGroups
     * @return Section
     */
    public function setBenchmarkGroups($benchmarkGroups)
    {
        $this->benchmarkGroups = $benchmarkGroups;
        return $this;
    }

    public function addBenchmarkGroups($benchmarkGroups)
    {
        foreach ($benchmarkGroups as $group) {
            $this->benchmarkGroups->add($group);
        }
        return $this;
    }

    public function removeBenchmarkGroups($benchmarkGroups)
    {
        foreach ($benchmarkGroups as $group) {
            $this->benchmarkGroups->removeElement($group);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return Section
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComboPrice()
    {
        return $this->comboPrice;
    }

    /**
     * @param mixed $comboPrice
     * @return Section
     */
    public function setComboPrice($comboPrice)
    {
        $this->comboPrice = $comboPrice;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStudy()
    {
        return $this->study;
    }

    /**
     * @param mixed $study
     * @return Section
     */
    public function setStudy($study)
    {
        $this->study = $study;
        return $this;
    }
}
