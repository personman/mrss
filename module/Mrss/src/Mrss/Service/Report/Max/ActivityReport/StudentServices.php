<?php

namespace Mrss\Service\Report\Max\ActivityReport;

use Mrss\Service\Report\Max\ActivityReport;

class StudentServices extends ActivityReport
{
    protected function getTopLevelBenchmarkKey($activity)
    {
        return "ss_{$activity}_cost_per_fte_student";
    }

    public function getActivities()
    {
        return array(
            'admissions' => 'Admissions',
            'recruitment' => 'Recruitment',
            'advising' => 'Advising',
            'counseling' => 'Counseling',
            'career' => 'Career Services',
            'financial_aid' => 'Financial Aid',
            'registrar' => 'Registrar / Student Records',
            'tutoring' => 'Tutoring',
            'testing' => 'Testing Services',
            'cocurricular' => 'Co-curricular Activities',
            'disabserv' => 'Disability Services',
            'vetserv' => 'Veterans Services'
        );
    }

    protected function getDetailColumns($activity)
    {
        $fields = array(
            "ss_{$activity}_cost_per_fte_emp" => "Average Salary and Benefits",
            "ss_{$activity}_students_per_fte_emp" => "FTE Students per Staff Person",
            "ss_salaries_perc_of_{$activity}" => "% of Costs for Salaries and Benefits",
            "ss_o_cost_perc_of_{$activity}" => "% of Costs for Non-labor Operating Costs",
            "ss_contract_perc_of_{$activity}" => "% of Costs for Contract Services"
        );

        return $fields;
    }
}
