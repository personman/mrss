<?php

namespace Mrss\Service\Report\Max\ActivityReport;

use Mrss\Service\Report\Max\ActivityReport;

class Instructional extends ActivityReport
{
    protected function getTopLevelBenchmarkKey($activity)
    {
        return "inst_cost_per_fte_student_$activity";
    }

    protected function getDetailColumns($activity)
    {
        $fields = array(
            "inst_cost_full_{$activity}" => "FT Faculty % of Time Spent",
            "inst_cost_part_{$activity}" => "PT Faculty % of Time Spent",
            "inst_cost_full_per_fte_student_{$activity}" => "FT Faculty $/FTE Students",
            "inst_cost_part_per_fte_student_{$activity}" => "PT Faculty $/FTE Students",
            "inst_cost_per_full_fte_faculty_{$activity}" => "FT Labor Cost per FTE Faculty",
            "inst_cost_per_part_fte_faculty_{$activity}" => "PT Labor Cost per FTE Faculty",
        );

        return $fields;
    }
}
