<?php

namespace Mrss\Service\Report\Max\ActivityReport;

use Mrss\Service\Report\Max\ActivityReport;

class AcademicSupport extends ActivityReport
{
    protected function getTopLevelBenchmarkKey($activity)
    {
        return "as_{$activity}_cost_per_fte_student";
    }

    public function getActivities()
    {
        return array(
            'tech' => 'Instructional Technology Support',
            'library' => 'Library Services',
            'experiential' => 'Experiential Education'
        );
    }

    protected function getDetailColumns($activity)
    {
        $fields = array(
            "as_{$activity}_cost_per_fte_emp" => "Average Salary and Benefits",
            "as_fte_students_per_{$activity}_fte_emp" => "FTE Students per Staff Person",
            "as_{$activity}_percent_salaries" => "% of Costs for Salaries and Benefits",
            "as_{$activity}_percent_o_cost" => "% of Costs for Non-labor Operating Costs"
            // @todo: confirm equations are right and add benchmarks for contract costs
        );

        return $fields;
    }
}
