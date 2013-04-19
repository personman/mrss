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


    // NCCBP form 2

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_f_yminus4_headc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_f_yminus4_degr_cert;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_perc_comp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_f_yminus4_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_perc_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_perc_comp_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_f_yminus4_headc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_f_yminus4_degr_cert;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_perc_comp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_f_yminus4_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_perc_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_perc_comp_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $f_yminus7_headc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_yminus7_degr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_yminus7_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_minus7perc_comp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $percminus7_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $percminus7_comtran;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_fminus7_headc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_yminus7_degr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_yminus7_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_perminus7_comp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_percminus7_tran;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_pminus7_comtran;


    // NCCBP form 3
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_stud_trans;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $fst_yr_gpa;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fst_yr_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $enro_next_yr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $avrg_1y_crh;

    // NCCBP form 4

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_cr_st;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $grad_bef_spr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $enr_bef_spr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $enr_fall;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $grad_bef_fall;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $next_term_pers;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $fall_fall_pers;

    // NCCBP form 5

    // Class properties cannot begin with a number
    //protected $96_exp;
    //protected $97_ova_exp;
    //protected $98_enr_again;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ac_adv_coun;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ac_serv;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $adm_fin_aid;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $camp_clim;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $camp_supp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $conc_indiv;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $instr_eff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $reg_eff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $resp_div_pop;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $safe_sec;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $serv_exc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $stud_centr;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $act_coll_learn;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $stud_eff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $acad_chall;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $stud_fac_int;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $sup_learn;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $choo_again;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ova_impr;

    // NCCBP form 6
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $grads_comp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $leave_noncomp;

    // NCCBP form 7
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_grad_abcpdfw;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_grad_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_grad_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $enr_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $comp_succ;

    // NCCBP form 8
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_tot_grad_dev_rem;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_tot_grad_dev_rem;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rw_tot_grad_dev_rem;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rw_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rw_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $r_tot_grad_dev_rem;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $r_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $r_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rw_ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $r_ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_enr_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_enr_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rw_enr_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $r_enr_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_comp_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_comp_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $rw_comp_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $r_comp_succ;

    // NCCBP form 9
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_tot_abcp_hld;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_tot_abcp_hld;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_enr_coll_cour;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_enr_coll_cour;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_compl_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_compl_abcpdf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_compl_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_compl_abcp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_coll_lev_ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_coll_lev_ret_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_coll_lev_enr_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_coll_lev_enr_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $m_coll_lev_compl_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $w_coll_lev_compl_rate;


    // NCCBP form 10
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_compl;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $no_tot_empl_rel;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $no_tot_purs_edu;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_resp_empl;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $no_tot_purs_edu_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $no_tot_emp_rel_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $emp_satis_prep_perc;

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

    /**
     * Hydrator method for putting form values into entity
     *
     * @param array $observationArray
     */
    public function populate($observationArray)
    {
        foreach ($observationArray as $key => $value) {
            if ($this->has($key)) {
                $this->set($key, $value);
            }
        }
    }
}
