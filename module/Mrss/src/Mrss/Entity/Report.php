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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="ReportItem", mappedBy="report")
     * @ORM\OrderBy({"sequence" = "ASC"})
     */
    protected $items;

    /**
     * @param mixed $college
     */
    public function setCollege($college)
    {
        $this->college = $college;
    }

    /**
     * @return mixed
     */
    public function getCollege()
    {
        return $this->college;
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

    public function getItems()
    {
        return $this->items;
    }
}
