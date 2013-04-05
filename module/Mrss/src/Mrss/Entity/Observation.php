<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mrss\Entity\Exception;
use Zend\Debug\Debug;

/** @ORM\Entity
 * @ORM\Table(name="observations")
 */
class Observation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $year;

    /**
     * @ORM\Column(type="float")
     */
    protected $cipCode;

    /**
     * @ORM\ManyToOne(targetEntity="College", inversedBy="observations")
     */
    protected $college;


    // NCCBP form 18:
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_undup_cr_hd;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_career_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_counc_adv_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_recr_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_fin_aid_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_stud_act_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_test_ass_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $career_staff_ratio;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $couns_adv_ratio;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $recr_staff_ratio;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $fin_aid_staff_ratio;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $stud_act_staff_ratio;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $test_ass_staff_ratio;


    public function getYear()
    {
        return $this->year;
    }

    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setCollege(College $college)
    {
        $this->college = $college;

        return $this;
    }

    public function getCollege()
    {
        return $this->college;
    }

    public function setCipCode($cipCode)
    {
        $this->cipCode = $cipCode;

        return $this;
    }

    public function getCipCode()
    {
        return $this->cipCode;
    }

    /**
     * @param $benchmark
     * @return mixed
     * @throws Exception\InvalidBenchmarkException
     */
    public function get($benchmark)
    {
        if (!property_exists($this, $benchmark)) {
            throw new Exception\InvalidBenchmarkException(
                "'$benchmark' is not a valid benchmark."
            );
        }

        return $this->$benchmark;
    }

    public function set($benchmark, $value)
    {
        if (!property_exists($this, $benchmark)) {
            throw new Exception\InvalidBenchmarkException(
                "'$benchmark' is not a valid benchmark."
            );
        }

        $this->$benchmark = $value;

        return $this;
    }
}
