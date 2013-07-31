<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

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
}
