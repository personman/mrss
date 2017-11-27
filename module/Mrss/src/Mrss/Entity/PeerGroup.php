<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Criteria;

/**
 * Steps for adding a demographic criteria
 *
 * 1) Add property, getter, setter in this file.
 * 2) Add new getter to the hasCriteria() method in this file.
 * 3) Generate and run migration to add db column.
 * 4) Add new field and inputFilter to Form/PeerComparisonDemographics.php.
 * 5) Add output for the new criteria to peer.phtml.
 * 6) Add new filter to college model's findByPeerGroup() method.
 *
 * @ORM\Entity
 * @ORM\Table(name="peer_groups")
 */
class PeerGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $year;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var College
     */
    protected $college;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="peerGroups")
     * @ORM\JoinColumn(
     * name="user_id",
     * referencedColumnName="id",
     * onDelete="CASCADE"
     * )
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Study")
     */
    protected $study;

    /** @ORM\Column(type="string") */
    protected $name;

    /** @ORM\Column(type="text", nullable=true) */
    protected $benchmarks;

    /** @ORM\Column(type="text", nullable=true) */
    protected $peers;

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function setCollege($college)
    {
        $this->college = $college;

        return $this;
    }

    public function getCollege()
    {
        return $this->college;
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }


    public function setStudy($study)
    {
        $this->study = $study;

        return $this;
    }


    public function getStudy()
    {
        return $this->study;
    }

    /**
     * @param integer $year
     * @return $this
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public function setBenchmarks($benchmarks)
    {
        $this->benchmarks = implode('|', $benchmarks);

        return $this;
    }

    public function getBenchmarks()
    {
        if ($this->benchmarks) {
            $benchmarks = explode('|', $this->benchmarks);
        } else {
            $benchmarks = array();
        }

        return $benchmarks;
    }

    public function setPeers($peers)
    {
        $this->peers = implode('|', $peers);

        return $this;
    }

    /**
     * @return array
     */
    public function getPeers($includeThisCollege = false)
    {
        if ($this->peers) {
            $peers = explode('|', $this->peers);
        } else {
            $peers = array();
        }

        if ($includeThisCollege) {
            $peers[] = $this->getUser()->getCollege()->getId();
        }

        return $peers;
    }

    public function removePeer($collegeId)
    {
        $peers = $this->getPeers();
        $newPeers = array();
        foreach ($peers as $peerId) {
            if ($peerId != $collegeId) {
                $newPeers[] = $peerId;
            }
        }

        $this->setPeers($newPeers);
    }

    public function addPeer($collegeId)
    {
        $peers = $this->getPeers();
        if (!in_array($collegeId, $peers)) {
            $peers[] = $collegeId;
        }

        $this->setPeers($peers);
    }

    public function getPeerCount()
    {
        return count($this->getPeers());
    }

    public function parseRange($range)
    {
        $parts = explode('-', $range);
        $min = intval(trim($parts[0]));
        $max = intval(trim($parts[1]));

        return array(
            'min' => $min,
            'max' => $max
        );
    }
}
