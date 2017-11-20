<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity
 * @ORM\Table(name="system_memberships")
 */
class SystemMembership
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="College", inversedBy="systemMemberships")
     * @ORM\JoinColumn(name="college_id", referencedColumnName="id", onDelete="CASCADE")
     * @var College
     */
    protected $college;

    /**
     * @ORM\ManyToOne(targetEntity="System", inversedBy="memberships")
     * @ORM\JoinColumn(name="system_id", referencedColumnName="id", onDelete="CASCADE")
     * @var System
     */
    protected $system;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $year;

    /**
     * @ORM\Column(type="string")
     */
    protected $dataVisibility;

    /**
     * @Gedmo\Mapping\Annotation\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return SystemMembership
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return College
     */
    public function getCollege()
    {
        return $this->college;
    }

    /**
     * @param College $college
     * @return SystemMembership
     */
    public function setCollege($college)
    {
        $this->college = $college;
        return $this;
    }

    /**
     * @return System
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * @param System $system
     * @return SystemMembership
     */
    public function setSystem($system)
    {
        $this->system = $system;
        return $this;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     * @return SystemMembership
     */
    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDataVisibility()
    {
        return $this->dataVisibility;
    }

    /**
     * @param mixed $dataVisibility
     * @return SystemMembership
     */
    public function setDataVisibility($dataVisibility)
    {
        $this->dataVisibility = $dataVisibility;
        return $this;
    }

    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }
}
