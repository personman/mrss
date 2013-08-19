<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilter;

/**
 * The queue for payment postbacks
 *
 * @ORM\Entity
 * @ORM\Table(name="payment_queue")
 */
class Payment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $transId;

    /**
     * @ORM\Column(type="text")
     */
    protected $postback;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $processed;

    /**
     * @Gedmo\Mapping\Annotation\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $processedDate;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTransId($transId)
    {
        $this->transId = $transId;

        return $this;
    }

    public function getTransId()
    {
        return $this->transId;
    }

    /**
     * Pass in an array, convert to json and store
     *
     * @param array $postback
     * @return $this
     */
    public function setPostback($postback)
    {
        $postback = json_encode($postback);
        $this->postback = $postback;

        return $this;
    }

    public function getPostback()
    {
        return json_decode($this->postback);
    }

    public function setProcessed($processed)
    {
        $this->processed = $processed;

        return $this;
    }

    public function getProcessed()
    {
        return $this->processed;
    }

    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setProcessedDate($processedDate)
    {
        $this->processedDate = $processedDate;

        return $this;
    }

    public function getProcessedDate()
    {
        return $this->processedDate;
    }
}
