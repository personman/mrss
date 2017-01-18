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
     * JSON string of agreement form data
     * @ORM\Column(type="string", nullable=true)
     */
    protected $sections;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date;

    /**
     * The subscription we are editing (if any)
     * @ORM\OneToOne(targetEntity="Subscription")
     */
    private $subscription;

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

    public function getIpeds()
    {
        $formData = $this->getFormData();

        if ($formData) {
            $formData = json_decode($formData, true);

            if (!empty($formData['institution']['ipeds'])) {
                return $formData['institution']['ipeds'];
            }
        }

        return false;
    }

    public function getCollegeId()
    {
        $formData = $this->getFormData();

        if ($formData) {
            $formData = json_decode($formData, true);

            if (!empty($formData['college_id'])) {
                return $formData['college_id'];
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getSections()
    {
        return $this->sections;
    }

    /**
     * @param mixed $sections
     * @return SubscriptionDraft
     */
    public function setSections($sections)
    {
        $this->sections = $sections;
        return $this;
    }

    /**
     * @return null|Subscription
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * @param mixed $subscription
     * @return SubscriptionDraft
     */
    public function setSubscription($subscription)
    {
        $this->subscription = $subscription;
        return $this;
    }

    public function isUpdate()
    {
        return (is_object($this->getSubscription()));
    }
}
