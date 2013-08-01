<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity
 * @ORM\Table(name="pages")
 */
class Page
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="string") */
    protected $title;

    /** @ORM\Column(type="string") */
    protected $route;

    /** @ORM\Column(type="text") */
    protected $content;

    /** @ORM\Column(type="string") */
    protected $status;

    /** @ORM\Column(type="datetime") */
    protected $created;

    /** @ORM\Column(type="datetime") */
    protected $updated;

    /** @ORM\Column(type="boolean") */
    protected $showTitle;

    /** @ORM\Column(type="boolean") */
    protected $showWrapper;

    /**
     * @ORM\ManyToMany(targetEntity="Study")
     * @ORM\JoinTable(name="pages_studies")
     */
    protected $studies;

    public function __construct()
    {
        $this->studies = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setCreated(\DateTime $created)
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setUpdated(\DateTime $updated)
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated()
    {
        return $this->updated;
    }

    public function setShowTitle($showTitle)
    {
        $this->showTitle = $showTitle;

        return $this;
    }

    public function getShowTitle()
    {
        return $this->showTitle;
    }

    public function setShowWrapper($showWrapper)
    {
        $this->showWrapper = $showWrapper;

        return $this;
    }

    public function getShowWrapper()
    {
        return $this->showWrapper;
    }

    public function setStudies($studies)
    {
        $this->studies = $studies;

        return $this;
    }

    public function getStudies()
    {
        return $this->studies;
    }

    public function addStudies($studies)
    {
        foreach ($studies as $study) {
            $this->studies->add($study);
        }
    }

    public function removeStudies($studies)
    {
        foreach ($studies as $study) {
            $this->studies->removeElement($study);
        }
    }
}
