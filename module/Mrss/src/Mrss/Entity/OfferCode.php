<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mrss\Entity\Study;

/** @ORM\Entity
 * @ORM\Table(name="offer_codes")
 */
class OfferCode
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
    protected $code;

    /**
     * @ORM\Column(type="float")
     */
    protected $price;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $skipOtherDiscounts;

    /**
     * @ORM\ManyToOne(targetEntity="Study")
     */
    protected $study;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setSkipOtherDiscounts($skip)
    {
        $this->skipOtherDiscounts = $skip;

        return $this;
    }

    public function getSkipOtherDiscounts()
    {
        return $this->skipOtherDiscounts;
    }

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
    }
}
