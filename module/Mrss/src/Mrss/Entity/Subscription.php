<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity
 * @ORM\Table(name="subscriptions")
 */
class Subscription
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    protected $year;

    /**
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="College", inversedBy="subscriptions")
     */
    protected $college;

    /**
     * @ORM\OneToOne(targetEntity="Observation")
     */
    protected $observation;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentMethod;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $paymentAmount;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $paymentDate;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentSystemName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentAddress;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentAddress2;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentCity;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentState;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentZip;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentEmail;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentTransactionId;
}
