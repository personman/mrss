<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity
 * @ORM\Table(name="colleges")
 */
class College
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="string") */
    protected $name;

    /** @ORM\Column(type="string") */
    protected $ipeds;

    /** @ORM\Column(type="string", length=10) */
    protected $city;

    /** @ORM\Column(type="float") */
    protected $latitude;

    /** @ORM\Column(type="float") */
    protected $longitude;


    public function getName()
    {
        return $this->name;
    }
}
