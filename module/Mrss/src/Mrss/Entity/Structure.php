<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Structure
 *
 * @ORM\Entity
 * @ORM\Table(name="structures")

 */
class Structure
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;


    /** @ORM\Column(type="text", nullable=true) */
    protected $json = null;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Structure
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJson()
    {
        if (empty($this->json)) {
            $this->json = '[]';
        }

        return $this->json;
    }

    /**
     * @param mixed $json
     * @return Structure
     */
    public function setJson($json)
    {
        $this->json = $json;
        return $this;
    }
}
