<?php

namespace Mrss\Service\Report;

class NonCredit extends Executive
{
    protected $sortPercentileCharts = true;

    public function getExecutiveReportConfig($year)
    {
        $config = array(
            'top-left' => array(
                'title' => '% of Continuing Education Enrollment of Total College Enrollment',
                'stacked' => false,
                'legend' => false,
                'percent' => true,
                'benchmarks' => array(
                    'enrollment_information_ce_enrollment_percent' => '',
                ),
                'description' => '* Chart description goes here. *'

            ),
            'top-right' => array(
                'title' => 'Program Enrollment',
                'stacked' => false,
                //'legend' => false,
                'type' => 'bar',
                'percentiles' => array(50),
                'flipData' => true,
                'percent' => true,
                'benchmarks' => array(
                    'll_enrollment_percent' => 'Life and Leisure',
                    'enrollment_information_workforce_enrollment_percent' => 'Workforce Training',
                    'abe_unduplicated_percent' => 'Adult Basic Education',
                ),
                'description' => '* Chart description goes here. *'
            ),
            'two-left' => array(
                'title' => 'Continuing Education Course Cancellation Rate',
                'stacked' => false,
                'legend' => false,
                'percent' => true,
                'benchmarks' => array(
                    'all_ce_cancellation_rate' => 'Cancellation Rate',
                ),
                'description' => '* Chart description goes here. *'
            ),
            'two-right' => array(
                'title' => 'Course Cancellation Rate by Program',
                'stacked' => false,
                'percent' => true,
                'type' => 'bar',
                'percentiles' => array(50),
                'flipData' => true,
                'benchmarks' => array(
                    'll_cancellation_rate' => 'Life and Leisure',
                    'enrollment_information_cancellation_rate' => 'Workforce Training',
                    'abe_cancellation_rate' => 'Adult Basis Education',
                ),
                'description' => '* Chart description goes here. *'
            ),
            // @todo: Retention by program

            'three-left' => array(
                'title' => 'Retention by Program',
                'stacked' => false,
                'percent' => true,
                'type' => 'bar',
                'flipData' => true,
                'percentiles' => array(50),
                'benchmarks' => array(
                    'retention_percent_returning_ll_students' => 'Life and Leisure',
                    'retention_percent_returning_students' => 'Workforce Training',
                    'retention_percent_returning_abe_students' => 'Adult Basis Education',
                    'retention_percent_returning_ce_students' => 'Continuing Education'
                ),
                'description' => '* Chart description goes here. *'
            ),
            'three-right' => array(
                'title' => 'Transition from Non-credit to Credit',
                'stacked' => false,
                'percent' => true,
                'percentiles' => array(50),
                'type' => 'bar',
                'flipData' => true,
                'benchmarks' => array(
                    'transition_ll_students' => 'Life and Leisure',
                    'transition_students' => 'Workforce Training',
                    'transition_abe_students' => 'Adult Basis Education',
                    'transition_ce_students' => 'Continuing Education'
                ),
                'description' => '* Chart description goes here. *'
            ),
            'four-left' => array(
                'title' => 'Market Penetration',
                'stacked' => false,
                'percent' => false,
                'benchmarks' => array(
                    'enrollment_information_market_penetration' => 'All Companies',
                    'contract_training_mkt_pen_50' => 'Large Companies',
                ),
                'description' => '* Chart description goes here. Test *'
            ),
            // @todo: contract training retention
            'four-right' => array(
                'title' => 'Contract Training Retention',
                'stacked' => false,
                'percent' => true,
                'legend' => false,
                'benchmarks' => array(
                    'retention_percent_returning_organizations_served' => 'Retention'
                ),
                'description' => '* Chart description for contract training retention goes here. *'
            ),
            'five-left' => array(
                'title' => 'Continuing Education Revenue and Expenses',
                'stacked' => false,
                'percent' => false,
                'benchmarks' => array(
                    'revenue_total' => 'Revenue',
                    'expenditures_total' => 'Expenses'
                ),
                'description' => '* Chart description goes here for exp/rev. *'
            ),
            'five-right' => array(
                'title' => 'Revenue and Expenses by Program',
                'stacked' => false,
                'type' => 'bar',
                'flipData' => true,
                'percent' => false,
                'percentiles' => array(50),
                'benchmarks' => array(
                    'revenue_ll_percent' => 'Life and Leisure Revenue',
                    'revenue_continuing_education_percent' => 'WT Revenue',
                    'revenue_contract_training_percent' => 'CT Revenue',
                    'revenue_abe_percent' => 'ABE Revenue',
                    'expenditures_ll_percent' => 'Life and Leisure Exp',
                    'expenditures_continuing_education_percent' => 'WT Exp',
                    'expenditures_contract_training_percent' => 'CT Exp',
                    'expenditures_abe_percent' => 'ABE Exp'

                ),
                'description' => '* Chart description for exp/rev by program goes here. *'
            ),
            'six-left' => array(
                'title' => 'Staffing',
                'stacked' => false,
                'percent' => false,
                'type' => 'bar',
                'flipData' => true,
                'percentiles' => array(50),
                'benchmarks' => array(
                    'staffing_full_time_instructors_percent' => 'Full-time',
                    'staffing_part_time_instructors_percent' => 'Part-time',
                    'staffing_independent_contractors_percent' => 'Contractors'
                ),
                'description' => '* Chart description for staffing goes here. *'
            ),
            'six-right' => array(
                'title' => 'Instructor/Staff Ratio',
                'stacked' => false,
                'percent' => false,
                'legend' => false,
                'benchmarks' => array(
                    'staffing_instructor_staff_ratio' => 'Instructor/Staff Ratio'
                ),
                'description' => '* Chart description for ratio goes here. *'
            ),
            'seven-left' => array(
                'title' => 'Expenses by Category',
                'stacked' => false,
                'percent' => false,
                'type' => 'bar',
                'flipData' => true,
                'percentiles' => array(50),
                'benchmarks' => array(
                    // @todo
                    'expenditures_salaries_percent' => 'Salaries',
                    'expenditures_benefits_percent' => 'Benefits',
                    'expenditures_supplies_percent' => 'Supplies',
                    'expenditures_marketing_percent' => 'Marketing',
                    'expenditures_capital_equipment_percent' => 'Equipment',
                    'expenditures_travel_percent' => 'Travel'
                ),
                'description' => '* Chart description for exp by category goes here. *'
            ),
            'seven-right' => array(
                'title' => 'Return on Investment',
                'stacked' => false,
                'percent' => false,
                'legend' => false,
                'benchmarks' => array(
                    'retained_revenue_roi' => 'Return on Investment'
                ),
                'description' => '* Chart description for ratio goes here. *'
            ),
        );

        unset($year);

        return $config;
    }

    protected function setUpSeriesColors()
    {
        $colorConfig = $colors = array(
            'seriesColors' => array(
                //'#9cc03e', // '#005595' lightened 40%
                '#3366B4', // '#519548' lightened 30%
                '#9b62c9',
                '#999',
                '#ebb164',
                '#F55',
                '#5F5',
                '#55F',
                '#5FF'
            ),
            'yourCollegeColors' => array(
                //'#507400',
                '#001A68',
                '#65318F',
                '#555',
                '#db891b',
                '#F00',
                '#0F0',
                '#00F',
                '#0FF',
            )
        );
        // What color will the bar be?
        $this->seriesColors = $colorConfig['seriesColors'];
        $this->yourCollegeColors = $colorConfig['yourCollegeColors'];
    }
}
