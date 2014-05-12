<?php
/**
 * Store draft subscriptions in the database so we can complete subscriptions on credit card postback
 * and so we can diagnose subscription problems.
 */

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/** @ORM\Entity
 * @ORM\Table(name="subscription_drafts")
 */
class SubscriptionDraft
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * JSON string of subscription form data
     * @ORM\Column(type="text", nullable=true)
     */
    protected $formData;

    /**
     * JSON string of agreement form data
     * @ORM\Column(type="text", nullable=true)
     */
    protected $agreementData;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $ip;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setFormData($data)
    {
        $this->formData = $data;

        return $this;
    }

    public function getFormData()
    {
        return $this->formData;
    }

    public function setAgreementData($data)
    {
        $this->agreementData = $data;

        return $this;
    }

    public function getAgreementData()
    {
        return $this->agreementData;
    }

    public function setDate(DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    public function getIp()
    {
        return $this->ip;
    }

}
