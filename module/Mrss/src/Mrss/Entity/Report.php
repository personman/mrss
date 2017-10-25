<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Report
 *
 * Custom reports populated with charts
 *
 * @ORM\Entity
 * @ORM\Table(name="reports")
 */
class Report
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Study")
     */
    protected $study;

    /**
     * @ORM\ManyToOne(targetEntity="College")
     * @ORM\JoinColumn(
     * name="college_id",
     * referencedColumnName="id",
     * onDelete="CASCADE",
     * nullable=true
     * )
     */
    protected $college;

    /**
     * System param is optional
     *
     * @ORM\ManyToOne(targetEntity="System")
     * @ORM\JoinColumn(
     * name="system_id",
     * referencedColumnName="id",
     * onDelete="CASCADE",
     * nullable=true
     * )
     */
    protected $system;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(
     * name="user_id",
     * referencedColumnName="id",
     * onDelete="CASCADE",
     * nullable=true
     * )
     */
    protected $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="ReportItem", mappedBy="report")
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    protected $items;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $sourceReportId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $permission;

    /**
     * @param mixed $college
     */
    public function setCollege($college)
    {
        $this->college = $college;
    }

    /**
     * @return \Mrss\Entity\College
     */
    public function getCollege()
    {
        return $this->college;
    }


    /**
     * @param \Mrss\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return null|\Mrss\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $study
     */
    public function setStudy($study)
    {
        $this->study = $study;
    }

    /**
     * @return mixed
     */
    public function getStudy()
    {
        return $this->study;
    }

    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return \Mrss\Entity\ReportItem[]|array
     */
    public function getItems()
    {
        return $this->items;
    }

    public function setSourceReportId($id)
    {
        $this->sourceReportId = $id;

        return $this;
    }

    public function getSourceReportId()
    {
        return $this->sourceReportId;
    }

    /**
     * @return mixed
     */
    public function getSystem()
    {
        return $this->system;
    }

    /**
     * @param mixed $system
     * @return Report
     */
    public function setSystem($system)
    {
        $this->system = $system;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param mixed $permission
     * @return Report
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
        return $this;
    }

    public function isPublic()
    {
        return ($this->getPermission() == 'public');
    }
}
