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
            "ss_{$activity}_percent_salaries" => "% of Costs for Salaries and Benefits",
            "ss_{$activity}_percent_o_cost" => "% of Costs for Non-labor Operating Costs"
            // @todo: confirm equations are right and add benchmarks for contract costs
        );

        return $fields;
    }
}
