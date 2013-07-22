<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @ORM\Column(type="string")
     */
    protected $ipeds;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $address2;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $city;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    protected $state;

    /**
     * @ORM\Column(type="string", length=11, nullable=true)
     */
    protected $zip;

    /**
     * @ORM\OneToMany(targetEntity="College", mappedBy="system")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $colleges;

    public function __construct()
    {
        $this->colleges = new ArrayCollection();
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

    public function getIpeds()
    {
        return $this->ipeds;
    }

    public function setIpeds($ipeds)
    {
        $this->ipeds = $ipeds;

        return $this;
    }


    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress2()
    {
        return $this->address2;
    }

    public function setAddress2($address)
    {
        $this->address2 = $address;

        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
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
