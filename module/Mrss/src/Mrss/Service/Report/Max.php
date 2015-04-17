<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;

class Max extends Report
{
    public function getActivities()
    {
        return array(
            'program_dev' => 'Program Development',
            'course_dev' => 'Course Development',
            'teaching' => 'Teaching',
            'tutoring' => 'Faculty Tutoring',
            'advising' => 'Faculty Advising',
            'ac_service' => 'Academic Services',
            'assessment' => 'Assessment',
            'prof_dev' => 'Professional Development'
        );
    }

    protected function getStudentServicesCostsFields()
    {
        return array(
            'Admissions' => array(
                'ss_admissions_cost_per_fte_student',
                null,
                'ss_admissions_students_per_fte_emp',
            ),
            'Recruitment' => array(
                'ss_recruitment_cost_per_fte_student',
                null,
                'ss_recruitment_students_per_fte_emp'
            ),
            'Advising' => array(
                'ss_advising_cost_per_fte_student',
                'ss_advising_cost_per_contact',
                'ss_advising_students_per_fte_emp'
            ),
            'Counseling' => array(
                'ss_counseling_cost_per_fte_student',
                'ss_counseling_cost_per_contact',
                'ss_counseling_students_per_fte_emp'
            ),
            'Career Services' => array(
                'ss_career_cost_per_fte_student',
                'ss_career_cost_per_contact',
                'ss_career_students_per_fte_emp'
            ),
            'Financial Aid' => array(
                'ss_financial_aid_cost_per_fte_student',
                'ss_financial_aid_cost_per_contact',
                'ss_financial_aid_students_per_fte_emp'
            ),
            'Registrar / Student Records' => array(
                'ss_registrar_cost_per_fte_student',
                null,
                'ss_registrar_students_per_fte_emp'
            ),
            'Tutoring' => array(
                'ss_tutoring_cost_per_fte_student',
                'ss_tutoring_cost_per_contact',
                'ss_tutoring_students_per_fte_emp'
            ),
            'Testing Services' => array(
                'ss_testing_cost_per_fte_student',
                'ss_testing_cost_per_contact',
                'ss_testing_students_per_fte_emp'
            ),
            'Co-curricular Activities' => array(
                'ss_cocurricular_cost_per_fte_student',
                null,
                'ss_cocurricular_students_per_fte_emp'
            ),
            'Disability Services' => array(
                'ss_disabserv_cost_per_fte_student',
                'ss_disabserv_cost_per_contact',
                'ss_disabserv_students_per_fte_emp'
            ),
            'Veterans Services' => array(
                'ss_vetserv_cost_per_fte_student',
                'ss_vetserv_cost_per_contact',
                'ss_vetserv_students_per_fte_emp'
            )
        );
    }

    protected function getAcademicSupportActivities()
    {
        return array(
            'as_tech_cost_per_fte_student' => 'Instructional Technology Support',
            'as_library_cost_per_fte_student' => 'Library Services',
            'as_experiential_cost_per_fte_student' => 'Experiential Education'
        );
    }
}
