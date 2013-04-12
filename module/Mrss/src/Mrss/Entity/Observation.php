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



    // NCCBP form 1:
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $unemp_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $med_hhold_inc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_cr_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_cr_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $trans_cred;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $t_c_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $dev_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $crd_stud_minc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $fem_cred_stud;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $non_res_alien;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $blk_n_hisp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ind_alaska;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $asia_pacif;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $hisp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $wht_n_hisp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $race_eth_unk;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tuition_fees;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $unre_o_rev;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $loc_sour;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $state_sour;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tuition_fees_sour;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $total_pop;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ipeds_enr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $non_cr_hdct;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $non_res_alien_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $race_eth_unk_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $hisp_anyrace_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ind_alaska_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $asian_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $blk_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $haw_pacific_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $white_2012;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $two_or_more;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $hs_stud_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pell_grant_rec;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $hs_stud_hdct;



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

    public function has($benchmark)
    {
        return property_exists($this, $benchmark);
    }


    /**
     * @param $benchmark
     * @return mixed
     * @throws Exception\InvalidBenchmarkException
     */
    public function get($benchmark)
    {
        if (!$this->has($benchmark)) {
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

    public function getArrayCopy()
    {
        $arrayCopy = array();
        foreach ($this as $key => $value) {
            $arrayCopy[$key] = $value;
        }

        return $arrayCopy;
    }
}
