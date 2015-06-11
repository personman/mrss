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
     * @ORM\Column(type="float", nullable=true)
     */
    protected $cipCode;

    /**
     * @ORM\ManyToOne(targetEntity="College", inversedBy="observations")
     */
    protected $college;

    /**
     * @ORM\OneToMany(targetEntity="SubObservation", mappedBy="observation")
     * @ORM\OrderBy({"id" = "ASC"})
     * @var SubObservation[]
     */
    protected $subObservations;

    /**
     * @ORM\OneToMany(targetEntity="Subscription", mappedBy="observation")
     * @var Subscription[]
     */
    protected $subscriptions;


    // MRSS

    // Form 1A
    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_expend;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_num;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_cred_hrs;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_expend;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_num;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_cred_hrs;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_pt_perc_credit_hr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_ft_perc_credit_hr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_exec_expend;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_exec_num;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_admin_expend;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_admin_num;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_expend_per_fte;

    // MRSS form 1 computed fields
    /** @ORM\Column(type="string", nullable=true) */
    protected $inst_cred_hrs_per_full_faculty;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_expend_per_cred_hr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_full_expend_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_expend_per_fte;

    /** @ORM\Column(type="string", nullable=true) */
    protected $inst_cred_hrs_per_part_faculty;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_expend_per_cred_hr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_part_expend_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_expend_per_cred_hr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_expend_per_fte_student;

    /** @ORM\Column(type="string", nullable=true) */
    protected $inst_expend_per_fte;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_exec_expend_per_fte;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_exec_expend_per_cred_hr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_exec_expend_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_expend_per_cred_hr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_expend_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_expend_per_employee;

    /** @ORM\Column(type="float", nullable=true) */
    protected $tuition_fees_per_cred_hr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_expend_o_rev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_expend_covered_by_tuition;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_net_rev_per_cred_hr;

    /** @ORM\Column(type="text", nullable=true) */
    protected $best_practices;

    /** @ORM\Column(type="text", nullable=true) */
    protected $best_practices_desc;

    /** @ORM\Column(type="text", nullable=true) */
    protected $on_campus_housing;

    /** @ORM\Column(type="text", nullable=true) */
    protected $four_year_degrees;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_admin_expend_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_o_cost_per_fte_student;



    // Form 1A (retired)
    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_course_planning;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admissions;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential;


    // MRSS Form 2
    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_num;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_cred_hr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_num;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_cred_hr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_non_labor_oper_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_prof_dev;

    // Some calculated variables for form 2
    /** @ORM\Column(type="float", nullable=true) */
    //protected $inst_cost_full_cred_hr_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_perc_taught_by_ft;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_perc_taught_by_pt;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_cred_hr_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_cred_hr_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_per_cred_hr_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_perc;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_perc;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_expend_per_fte_faculty;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_expend_per_fte_faculty;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_total_expend_per_fte_faculty;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_fte_students;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_fte_students_per_fte_faculty;


    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_cred_hr_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_cred_hr_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_cred_hr_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_cred_hr_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_cred_hr_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_cred_hr_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_cred_hr_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_cred_hr_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_cred_hr_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_cred_hr_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_cred_hr_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_cred_hr_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_cred_hr_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_cred_hr_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_cred_hr_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_cred_hr_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_cred_hr_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_cred_hr_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_cred_hr_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_cred_hr_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_cred_hr_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_cred_hr_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_cred_hr_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_cred_hr_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_perc_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_perc_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_perc_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_perc_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_perc_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_perc_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_perc_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_perc_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_perc_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_perc_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_perc_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_perc_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_perc_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_perc_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_perc_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_perc_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_fte_student_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_fte_student_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_fte_student_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_fte_student_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_fte_student_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_fte_student_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_fte_student_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_full_per_fte_student_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_fte_student_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_fte_student_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_fte_student_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_fte_student_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_fte_student_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_fte_student_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_fte_student_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_part_per_fte_student_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_fte_student_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_fte_student_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_fte_student_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_fte_student_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_fte_student_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_fte_student_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_fte_student_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_fte_student_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_full_fte_faculty_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_full_fte_faculty_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_full_fte_faculty_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_full_fte_faculty_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_full_fte_faculty_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_full_fte_faculty_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_full_fte_faculty_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_full_fte_faculty_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_part_fte_faculty_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_part_fte_faculty_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_part_fte_faculty_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_part_fte_faculty_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_part_fte_faculty_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_part_fte_faculty_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_part_fte_faculty_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_per_part_fte_faculty_prof_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_expend_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_expend_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_expend_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_expend_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_expend_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_expend_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_expend_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_cost_expend_prof_dev;




    // MRSS Form 3
    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_program_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_course_dev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_teaching;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_ac_service;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_assessment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $inst_total_other;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admissions_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admissions_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admis_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admiss_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_students;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv_o_students;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv_o_students;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admissions_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admissions_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutorings_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admissions_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admissions_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admissions_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_admissions_students_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_recruitment_students_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_students_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling_students_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_students_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_students_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_registrar_students_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_students_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_students_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_cocurricular_students_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv_students_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv_students_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_advising_cost_per_contact;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_counseling_cost_per_contact;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_career_cost_per_contact;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_financial_aid_cost_per_contact;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_tutoring_cost_per_contact;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_testing_cost_per_contact;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_disabserv_cost_per_contact;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_vetserv_cost_per_contact;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries_perc_of_admissions;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost_perc_of_admissions;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract_perc_of_admissions;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries_perc_of_recruitment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost_perc_of_recruitment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract_perc_of_recruitment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries_perc_of_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost_perc_of_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract_perc_of_advising;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries_perc_of_counseling;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost_perc_of_counseling;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract_perc_of_counseling;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries_perc_of_career;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost_perc_of_career;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract_perc_of_career;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries_perc_of_financial_aid;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost_perc_of_financial_aid;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract_perc_of_financial_aid;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries_perc_of_registrar;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost_perc_of_registrar;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract_perc_of_registrar;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries_perc_of_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost_perc_of_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract_perc_of_tutoring;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries_perc_of_testing;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost_perc_of_testing;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract_perc_of_testing;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries_perc_of_cocurricular;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost_perc_of_cocurricular;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract_perc_of_cocurricular;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries_perc_of_disabserv;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost_perc_of_disabserv;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract_perc_of_disabserv;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_salaries_perc_of_vetserv;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_o_cost_perc_of_vetserv;

    /** @ORM\Column(type="float", nullable=true) */
    protected $ss_contract_perc_of_vetserv;




    // MRSS Form 4
    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_contract;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_budget;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_percent_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_percent_o_cost;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library_cost_per_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_cost_per_fte_emp;

    /** @ORM\Column(type="string", nullable=true) */
    protected $inst_fte_students;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_cost_per_contact;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_cost_per_contact;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_library_cost_per_fte_student;

    /** @ORM\Column(type="string", nullable=true) */
    protected $as_fte_students_per_tech_fte_emp;

    /** @ORM\Column(type="string", nullable=true) */
    protected $as_fte_students_per_library_fte_emp;

    /** @ORM\Column(type="string", nullable=true) */
    protected $as_fte_students_per_experiential_fte_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_tech_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_experiential_cost_per_fte_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_students_per_tech_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_students_per_library_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_students_per_experiential_emp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_salaries_perc_of_tech;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_o_cost_perc_of_tech;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_contract_perc_of_tech;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_salaries_perc_of_library;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_o_cost_perc_of_library;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_contract_perc_of_library;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_salaries_perc_of_experiential;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_o_cost_perc_of_experiential;

    /** @ORM\Column(type="float", nullable=true) */
    protected $as_contract_perc_of_experiential;




    // MRSS Form 5 Demographics
    /** @ORM\Column(type="string", nullable=true) */
    protected $male_cred_stud;

    /** @ORM\Column(type="string", nullable=true) */
    protected $first_gen_students;

    /** @ORM\Column(type="string", nullable=true) */
    protected $institutional_control;

    /** @ORM\Column(type="string", nullable=true) */
    protected $institutional_type;

    /** @ORM\Column(type="float", nullable=true) */
    protected $part_time_credit_hours;

    /** @ORM\Column(type="float", nullable=true) */
    protected $full_time_credit_hours;

    /** @ORM\Column(type="float", nullable=true) */
    protected $full_time_credit_hours_percent;

    /** @ORM\Column(type="string", nullable=true) */
    protected $part_time_credit_hours_percent;

    /** @ORM\Column(type="string", nullable=true) */
    protected $instruction_dev;

    /** @ORM\Column(type="string", nullable=true) */
    protected $instruction_online;

    /** @ORM\Column(type="string", nullable=true) */
    protected $instruction_face_to_face;

    /** @ORM\Column(type="string", nullable=true) */
    protected $instruction_hybrid;

    /** @ORM\Column(type="string", nullable=true) */
    protected $Instructional_support;

    /** @ORM\Column(type="float", nullable=true) */
    protected $op_exp_inst;

    /** @ORM\Column(type="float", nullable=true) */
    protected $op_exp_student_services;

    /** @ORM\Column(type="float", nullable=true) */
    protected $op_exp_acad_supp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $op_exp_inst_support;

    /** @ORM\Column(type="float", nullable=true) */
    protected $op_exp_research;

    /** @ORM\Column(type="float", nullable=true) */
    protected $op_exp_pub_serv;

    /** @ORM\Column(type="float", nullable=true) */
    protected $op_exp_oper_n_maint;

    // MRSS forms 5 and 6 de-duplicated with NCCBP
    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_institutional_demographics_unemployment_rate;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_institutional_demographics_median_household_income;

    /** @ORM\Column(type="string", nullable=true) */
    protected $max_res_institutional_demographics_faculty_unionized;

    /** @ORM\Column(type="string", nullable=true) */
    protected $max_res_institutional_demographics_staff_unionized;

    /** @ORM\Column(type="string", nullable=true) */
    protected $max_res_institutional_demographics_campus_environment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_ipeds_enr;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_ft_cr_head;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_pt_cr_head;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_hs_stud_hdct;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_hs_stud_crh;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_pell_grant_rec;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_male_cred_stud;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_fem_cred_stud;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_first_gen_students;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_trans_cred;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_t_c_crh;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_dev_crh;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_crd_stud_minc;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_non_res_alien_2012;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_hisp_anyrace_2012;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_ind_alaska_2012;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_asian_2012;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_blk_2012;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_haw_pacific_2012;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_white_2012;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_two_or_more;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_race_eth_unk_2012;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_tuition_fees;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_unre_o_rev;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_loc_sour;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_state_sour;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_tuition_fees_sour;

    /** @ORM\Column(type="string", nullable=true) */
    protected $max_res_institutional_control;

    /** @ORM\Column(type="string", nullable=true) */
    protected $max_res_institutional_type;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_tot_fy_stud_crh;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_enrollment_information_duplicated_enrollment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_ft_tot_stud_crhrs_tght;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_pt_tot_stud_crhrs_tght;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_ft_f_yminus4_headc;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_op_exp_inst;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_ft_f_yminus4_degr_cert;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_op_exp_student_services;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_ft_f_yminus4_transf;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_op_exp_acad_supp;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_pt_f_yminus4_headc;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_op_exp_inst_support;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_pt_f_yminus4_degr_cert;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_op_exp_research;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_pt_f_yminus4_transf;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_op_exp_pub_serv;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_f_yminus7_headc;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_op_exp_oper_n_maint;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_ft_yminus7_degr;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_ft_yminus7_transf;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_pt_fminus7_headc;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_pt_yminus7_degr;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_pt_yminus7_transf;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_tot_grad_abcpdfw;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_tot_grad_abcpdf;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_tot_grad_abcp;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_tot_cr_st;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_grad_bef_spr;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_enr_bef_spr;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_grad_bef_fall;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $max_res_enr_fall;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_fall_fall_pers;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_next_term_pers;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_pt_perc_transf;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_ft_perc_comp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_pt_perc_comp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_ft_perc_transf;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_ft_perc_comp_transf;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_pt_perc_comp_transf;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_ft_minus7perc_comp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_percminus7_transf;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_percminus7_comtran;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_pt_perminus7_comp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_pt_percminus7_tran;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_pt_pminus7_comtran;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_ft_yminus3_perc_comp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_ft_yminus3_perc_transf;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_ft_yminus3_perc_comp_transf;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_pt_yminus3_perc_comp;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_pt_yminus3_perc_transf;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_pt_yminus3_perc_comp_transf;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_ret_rate;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_enr_succ;

    /** @ORM\Column(type="float", nullable=true) */
    protected $max_res_comp_succ;




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
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ft_cr_head_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $pt_cr_head_perc;

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

    // The following field is not used and can be deleted
    /** @ORM\Column(type="float", nullable=true) */
    protected $first_gen_stud;

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
    protected $restricted_o_rev;

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

    // Most of the campus information fields are on Workforce, too
    /** @ORM\Column(type="string", nullable=true) */
    protected $institutional_demographics_calendar;


    // NCCBP form 2
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_f_yminus3_headc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_f_yminus3_degr_cert;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ft_f_yminus3_degr_and_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_yminus3_perc_comp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_f_yminus3_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_yminus3_perc_transf;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_yminus3_perc_comp_transf;



    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_f_yminus4_headc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_f_yminus4_degr_cert;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ft_f_yminus4_degr_and_transf;

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
     * @ORM\Column(type="float", nullable=true)
     */
    protected $pt_f_yminus4_degr_and_transf;

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
    protected $ft_yminus7_degr_and_tranf;

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
    protected $pt_yminus7_degr_and_tranf;

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
    protected $tot_stud_trans_two_year;

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

    // Class properties cannot begin with a number, so prepend an 'n'
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $n96_exp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $n97_ova_exp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $n98_enr_again;

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

    /** @ORM\Column(type="float", nullable=true) */
    protected $empl_satis_prep;

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

    // NCCBP form 11

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ec_i_abcpdfw;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ec_ii_abcpdfw;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $al_abcpdfw;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $sp_abcpdfw;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ec_i_abcpdf;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ec_ii_abcpdf;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $al_abcpdf;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $sp_abcpdf;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ec_i_abcp;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $ec_ii_abcp;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $al_abcp;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $sp_abcp;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ec_i_retention_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ec_i_enr_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ec_i_comp_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ec_ii_retention_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ec_ii_enr_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $ec_ii_comp_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $al_retention_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $al_enr_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $al_comp_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $sp_retention_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $sp_enr_suc_rate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $sp_comp_suc_rate;


    // NCCBP form 12

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $a;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $b;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $c;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $d;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $f;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $p;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $w;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $total;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $a_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $b_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $c_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $p_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $d_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $f_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $w_perc;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $withdrawal;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $completed;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $compl_succ;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $form12_instw_cred_grad_enr_succ;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $anb;


    // NCCBP form 13a
    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $empl_inst_pop;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $serv_ar_min;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $empl_tot_inst_min_pop;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $stud_tot_inst_min_pop;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $stud_inst_pop;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $perc_inst_min;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $perc_inst_min_empl;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $stud_inst_serv_ratio;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $empl_inst_serv_ratio;


    // NCCBP form 13b:

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pub_hs_spr_hs_grad;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pub_hs_fall;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $priv_hs_spr_hs_grad;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $priv_hs_fall;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $tot_hs_spr_hs_grad;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $tot_tot_hs_fall;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $pub_perc_enr;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $priv_perc_enr;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $tot_perc_enr;


    // Form 14a
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $undup_cre_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $undup_non_cre_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $serv_pop;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $cre_stud_pen_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ncre_stud_pen_rate;


    // Form 14b
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $cul_act_dupl_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pub_meet_dupl_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $spo_dupl_head;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form14b_serv_pop;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $cul_com_part;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pub_com_part;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $spo_com_part;



    // Form 15
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $fy_dup_headc_bni;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $comp_serv;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_inst_adm_cst;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_rev;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $net_revenue_usd;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $net_revenue_perc;



    // Form 16a
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_cred_cou_sec;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_cred_stud;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $av_cred_sec_size;


    // Form 16B
    /** @ORM\Column(type="float", nullable=true) */
    protected $tot_fte_fac;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form16b_cred_co_stud_fac_tot_fte_stud;

    /**
     * @ORM\Column(type="float", length=20, nullable=true)
     */
    protected $stu_fac_ratio;


    // Form 16c
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_tot_fac;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_tot_stud_crhrs_tght;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_tot_cred_sec_tght;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_tot_fac;


    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_tot_stud_crhrs_tght;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_tot_cred_sec_tght;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fac;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_cred_hrs;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_perc_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_perc_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_cred_sec;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ft_perc_sec;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $pt_perc_sec;


    // Form 17a
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_dis_lear_stud_hrs;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_dis_lear_sec;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_crh_tght;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_crs_tght;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $dist_prop_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $dist_prop_crs;


    // Form 17b
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_a;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_b;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_c;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_d;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_p;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_f;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_w;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_total;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_a_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_b_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_c_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_d_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_p_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_f_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_w_perc;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_withdrawal;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_completed;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $completer_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_enr_succ;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $form17b_dist_learn_grad_anb;



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






    // Form 19a
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_ft_reg_empl;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ret;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $dep;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $ret_occ_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $dep_occ_rate;



    // Form 19b
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_empl;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $griev;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $harass;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $griev_occ_rate;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $harass_occ_rate;




    // Form 20a
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_dir_exp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fy_stud_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_stud;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $cst_crh;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $cst_fte_stud;

    // Form 20b
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_dev_train_exp;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_cred_fac;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_staff;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $tot_fte_empl;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $exp_fte_empl;




    // Workforce form 1
    /** @ORM\Column(type="integer", nullable=true) */
    protected $enrollment_information_duplicated_enrollment;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $enrollment_information_unduplicated_enrollment;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $enrollment_information_organizations_served;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $enrollment_information_training_contracts;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $enrollment_information_total_contact_hours;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $enrollment_information_courses_offered;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $enrollment_information_courses_canceled;

    // Workforce form 2
    /** @ORM\Column(type="integer", nullable=true) */
    protected $retention_returning_organizations;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $retention_returning_students;

    // Workforce form 3
    /** @ORM\Column(type="integer", nullable=true) */
    protected $staffing_full_time_instructors;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $staffing_part_time_instructors;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $staffing_independent_contractors;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $staffing_full_time_support_staff;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $staffing_part_time_support_staff;

    // Workforce form 4
    /** @ORM\Column(type="float", nullable=true) */
    protected $transition_students;

    // Workforce form 5
    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_federal;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_state;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_local;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_grants;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_earned_revenue;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_contract_training;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_continuing_education;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_other;

    /** @ORM\Column(type="text", nullable=true) */
    protected $revenue_other_specify;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_total;

    // Workforce form 6
    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_salaries;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_benefits;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_supplies;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_marketing;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_capital_equipment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_travel;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_for_other;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_contract_training;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_continuing_education;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_other;

    /** @ORM\Column(type="text", nullable=true) */
    protected $expenditures_other_specify;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_overhead;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_overhead_costs;

    // Workforce form 7
    /** @ORM\Column(type="string", nullable=true) */
    protected $retained_revenue_contract_training;

    /** @ORM\Column(type="float", nullable=true) */
    protected $retained_revenue_continuing_education;

    /** @ORM\Column(type="float", nullable=true) */
    protected $retained_revenue_total;

    /** @ORM\Column(type="string", nullable=true) */
    protected $net_revenue_contract_training;

    /** @ORM\Column(type="string", nullable=true) */
    protected $net_revenue_other;

    /** @ORM\Column(type="text", nullable=true) */
    protected $net_revenue_other_specify;

    /** @ORM\Column(type="float", nullable=true) */
    protected $net_revenue_continuing_education;

    /** @ORM\Column(type="float", nullable=true) */
    protected $net_revenue_total;

    /** @ORM\Column(type="float", nullable=true) */
    protected $retained_revenue_roi;



    // Workforce form 8
    /** @ORM\Column(type="float", nullable=true) */
    protected $satisfaction_client;

    /** @ORM\Column(type="float", nullable=true) */
    protected $satisfaction_student;

    // Workforce form 9
    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_credit_enrollment;

    /** @ORM\Column(type="float", nullable=true) */
    protected $institutional_demographics_operating_revenue;

    /** @ORM\Column(type="string", nullable=true) */
    protected $institutional_demographics_campus_environment;

    /** @ORM\Column(type="string", nullable=true) */
    protected $institutional_demographics_faculty_unionized;

    /** @ORM\Column(type="string", nullable=true) */
    protected $institutional_demographics_staff_unionized;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_total_population;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_total_companies;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_companies_less_than_50;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_companies_50_to_99;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_companies_100_to_499;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_companies_500_or_greater;

    /** @ORM\Column(type="float", nullable=true) */
    protected $institutional_demographics_unemployment_rate;

    /** @ORM\Column(type="float", nullable=true) */
    protected $institutional_demographics_median_household_income;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_credentials_awarded;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_certifications_awarded;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_licenses_awarded;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $institutional_demographics_certificates_awarded;

    /** @ORM\Column(type="float", nullable=true) */
    protected $enrollment_information_workforce_enrollment_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $enrollment_information_market_penetration;

    /** @ORM\Column(type="string", nullable=true) */
    protected $enrollment_information_contact_hours_per_student;

    /** @ORM\Column(type="float", nullable=true) */
    protected $retention_percent_returning_organizations_served;

    /** @ORM\Column(type="float", nullable=true) */
    protected $retention_percent_returning_students;

    /** @ORM\Column(type="float", nullable=true) */
    protected $staffing_full_time_instructors_percent;

    /** @ORM\Column(type="integer", nullable=true) */
    protected $staffing_part_time_instructors_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $staffing_independent_contractors_percent;

    /** @ORM\Column(type="string", nullable=true) */
    protected $staffing_instructor_staff_ratio;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_contract_training_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $revenue_continuing_education_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_salaries_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_benefits_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_supplies_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_marketing_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_capital_equipment_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_travel_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_contract_training_percent;

    /** @ORM\Column(type="float", nullable=true) */
    protected $expenditures_continuing_education_percent;



    // Test fields
    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $test_ass_staff_ratio;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $test_student_count;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $test_green_eye_count;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $test_green_eye_percentage;









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

    /**
     * @return \Mrss\Entity\College
     */
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

    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;
        return $this;
    }

    /**
     * @return Subscription[]
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    public function setSubObservations($subObservations)
    {
        $this->subObservations = $subObservations;

        return $this;
    }

    public function getSubObservations()
    {
        return $this->subObservations;
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

        // Convert empty strings to null so they don't end up as 0
        if ($value === '') {
            $value = null;
        }

        // Convert arrays to string
        if (is_array($value)) {
            $value = implode("\n", $value);
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

    public function getAllBenchmarks()
    {
        $benchmarks = array();
        $exclude = array('id', 'year', 'cipCode', 'college', 'subObservations');
        foreach ($this as $key => $value) {
            if (!in_array($key, $exclude)) {
                $benchmarks[] = $key;
            }
        }

        return $benchmarks;
    }

    /**
     * This is really only for MRSS
     */
    public function mergeSubobservations()
    {
        $this->sumSubobservations();

        $prefix = 'inst_cost_';
        $facultyTypes = array('full', 'part');
        $activities = array(
            'program_dev',
            'course_dev',
            'teaching',
            'tutoring',
            'advising',
            'ac_service',
            'assessment',
            'prof_dev'
        );

        foreach ($activities as $activity) {
            foreach ($facultyTypes as $facultyType) {
                // Build some property names
                $percentageField = $prefix . $facultyType . '_' . $activity;
                $activityCostField = $prefix . $facultyType . '_expend_' . $activity;
                $costField = $prefix . $facultyType . '_expend';
                //$perCreditHourField = $prefix . $facultyType .
                //    '_cred_hr_' . $activity;

                $totalCost = 0;
                $activityCost = 0;
                $activityPercentage = 0;

                //$percentagesOfTimeSpentOnActivity = array();

                // Get the total cost
                foreach ($this->getSubObservations() as $subobservation) {
                    $acCost = $subobservation->get($costField);
                    $totalCost += $acCost;
                }

                // Loop over the subobservations
                foreach ($this->getSubObservations() as $subobservation) {
                    $percentageSpentOn = $subobservation->get($percentageField);
                    $acCost = $subobservation->get($costField);

                    //$percentagesOfTimeSpentOnActivity[] = $percentageSpentOn;

                    // If we've got null values, skip it
                    if (!is_null($percentageSpentOn) || !is_null($acCost)) {
                        $cost = ($percentageSpentOn / 100) * $acCost;
                        $activityCost += $cost;
                    }
                }

                // Activity percentage
                if ($totalCost) {
                    $activityPercentage  = $activityCost / $totalCost * 100;
                } else {
                    $activityPercentage = 0;
                }

                // Now save the cost
                $this->set($activityCostField, $activityCost);
                $this->set($percentageField, $activityPercentage);

                // Average the percentages of time spent on the activity
                /*if (count($percentagesOfTimeSpentOnActivity)) {
                    $average = array_sum($percentagesOfTimeSpentOnActivity) /
                        count($percentagesOfTimeSpentOnActivity);
                } else {
                    $average = 0;
                }

                $this->set($percentageField, $average);*/
            }
        }
    }

    public function sumSubobservations()
    {
        $values = array(
            'inst_cost_full_cred_hr' => 0,
            'inst_cost_part_cred_hr' => 0,
            'inst_cost_full_expend' => 0,
            'inst_cost_part_expend' => 0,
            'inst_cost_full_num' => 0,
            'inst_cost_part_num' => 0
        );

        foreach ($this->getSubObservations() as $subObservation) {
            foreach ($values as $key => $value) {
                $values[$key] += $subObservation->get($key);
            }
        }

        // Save them
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }
}
