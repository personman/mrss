<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity
 * @ORM\Table(name="college_systems")
 */
class System
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="string") */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="College", mappedBy="system")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $colleges;

    public function __construct()
    {
        $this->colleges = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
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

    public function setColleges($colleges)
    {
        $this->colleges = $colleges;

        return $this;
    }

    public function getColleges()
    {
        return $this->colleges;
    }
}
