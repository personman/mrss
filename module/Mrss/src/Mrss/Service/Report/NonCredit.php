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
                'description' => 'Percentage of unduplicated continuing education enrollment of total college enrollment.'

            ),
            'top-right' => array(
                'title' => '% of Program Enrollment of Total College Enrollment',
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
                'description' => 'Percentage of unduplicated program enrollment of total college enrollment.'
            ),
            'two-left' => array(
                'title' => 'Continuing Education Course Cancellation Rate',
                'stacked' => false,
                'legend' => false,
                'percent' => true,
                'benchmarks' => array(
                    'all_ce_cancellation_rate' => 'Cancellation Rate',
                ),
                'description' => 'Percentage of continuing education courses cancelled of courses offered.'
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
                    'abe_cancellation_rate' => 'Adult Basic Education',
                ),
                'description' => 'Percentage of courses cancelled of courses offered by program.'
            ),
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
                    'retention_percent_returning_abe_students' => 'Adult Basic Education',
                    'retention_percent_returning_ce_students' => 'Continuing Education'
                ),
                'description' => 'Percentage of returning students of total enrolled Continuing Education students.'
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
                    'transition_abe_students' => 'Adult Basic Education',
                    'transition_ce_students' => 'Continuing Education'
                ),
                'description' => 'Percentage of continuing education students who enroll in a credit course.'
            ),
            'four-left' => array(
                'title' => 'Contract Training Retention',
                'stacked' => false,
                'percent' => true,
                'legend' => false,
                'benchmarks' => array(
                    'retention_percent_returning_organizations_served' => 'Retention'
                ),
                'description' => 'Percentage of returning organizations of total organizations served.'
            ),

            'four-right' => array(
                'title' => 'Contract Training Market Penetration',
                'subtitle' => 'Companies with 50+ Employees',
                'stacked' => false,
                'percent' => false,
                'benchmarks' => array(
                    //'enrollment_information_market_penetration' => 'All Companies',
                    'contract_training_mkt_pen_50' => 'Large Companies',
                ),
                'description' => 'Percentage of organizations served of total organizations with 50+ in the service area.'
            ),
            'five-left' => array(
                'title' => 'Continuing Education Revenue and Expenses',
                'stacked' => false,
                'percent' => false,
                'verticalLabels' => true,
                'benchmarks' => array(
                    'revenue_total' => 'Revenue',
                    'expenditures_total' => 'Expenses'
                ),
                'description' => 'Total Gross Revenue - Revenue in support of all non-credit continuing education - include all public, grant and earned revenue from contracting training, continuing education and other.  Total Expenditures - All expenditures resulting from non-credit continuing education - include expenditures from contracting training, continuing education and other and except for institutional or overhead costs.'
            ),
            'five-right' => array(
                'title' => 'Expenses by Category',
                'stacked' => false,
                'percent' => false,
                'type' => 'bar',
                'flipData' => true,
                'percentiles' => array(50),
                'benchmarks' => array(
                    'expenditures_salaries_percent' => 'Salaries',
                    'expenditures_benefits_percent' => 'Benefits',
                    'expenditures_supplies_percent' => 'Supplies',
                    'expenditures_marketing_percent' => 'Marketing',
                    'expenditures_capital_equipment_percent' => 'Equipment',
                    'expenditures_travel_percent' => 'Travel'
                ),
                'description' => 'Percentage of expenditures by total expenditures.'
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
                'description' => 'Percentage of type of instructors by total continuing education instructors.'
            ),
            'six-right' => array(
                'title' => 'Instructor/Staff Ratio',
                'stacked' => false,
                'percent' => false,
                'legend' => false,
                'benchmarks' => array(
                    'staffing_instructor_staff_ratio' => 'Instructor/Staff Ratio'
                ),
                'description' => 'Total instructors by total staff.'
            ),
            'seven-left' => array(
                'title' => 'Return on Investment',
                'stacked' => false,
                'percent' => false,
                'legend' => false,
                'benchmarks' => array(
                    'retained_revenue_roi' => 'Return on Investment'
                ),
                'description' => 'Total operating margins is total non-credit revenue minus total non-credit expenditures.'
            ),
            'seven-right' => array(
                'title' => 'Return on Investment by Program',
                'stacked' => false,
                'type' => 'bar',
                'flipData' => true,
                'percent' => false,
                'percentiles' => array(50),
                'benchmarks' => array(
                    'll_roi' => 'Life and Leisure',
                    'wf_roi' => 'Workforce Training',
                    'contract_training_roi' => 'Contract Training',
                    'abe_roi' => 'Adult Basic Education',
                ),
                'description' => 'Total operating margins is total non-credit revenue minus total non-credit expenditures by program.'
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
