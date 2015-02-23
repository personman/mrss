<?php

namespace Mrss\Service\Report\Max;

use Mrss\Service\Report;
use Mrss\Entity\Observation;
use Mrss\Entity\Benchmark;

class Internal extends Report
{
    protected $naPlaceholder = 'N/A';

    public function getInstructionalCosts(Observation $observation)
    {
        $data = array();
        $chartXCategories = array();
        $chartData = array();

        // The institution-wide value
        $dbColumn = 'inst_total_expend_per_fte_student';
        $benchmark = $this->getBenchmark($dbColumn);
        $rawValue = $observation->get($dbColumn);
        $value = $benchmark->format($rawValue);

        $data[] = array(
            'label' => 'Total Costs Per FTE Student',
            'value' => $value
        );

        $collegeName = $observation->getCollege()->getName();
        $chartXCategories[] = $collegeName;
        $chartData[] = $rawValue;

        // Now the subobservations (academic units)
        foreach ($observation->getSubObservations() as $subObservation) {
            $dbColumn = 'inst_cost_per_fte_student';
            $label = $subObservation->getName() . ' Total Cost Per FTE Student';
            $benchmark = $this->getBenchmark($dbColumn);
            $rawValue = $subObservation->get($dbColumn);
            $value = $benchmark->format($rawValue);

            $data[] = array(
                'label' => $label,
                'value' => $value
            );

            $chartXCategories[] = $subObservation->getName();
            $chartData[] = $rawValue;
        }

        $series = array(
            array(
                'data' => $chartData
            )
        );

        $chart = $this->getBarChart($benchmark, $chartXCategories, $series);
        //pr($chart);
        return array($data, $chart);
    }

    public function getBarChart(Benchmark $benchmark, $chartXCategories, $series, $title = null, $id = null)
    {
        $format = $this->getFormat($benchmark);

        if (is_null($title)) {
            $title = $benchmark->getDescriptiveReportLabel();
        }

        if (is_null($id)) {
            $id = $benchmark->getDbColumn();
        }

        $chart = array(
            'id' => 'chart_' . $id,
            'chart' => array(
                'type' => 'column',
                'events' => array(
                    'load' => 'loadChart'
                ),
            ),
            /*'exporting' => array(
                'chartOptions' => array(
                    'series' => $seriesWithDataLabels,
                )
            ),*/
            'colors' => $this->getColors(),
            'title' => array(
                'text' => $title,
            ),
            'xAxis' => array(
                'categories' => $chartXCategories,
                'tickLength' => 0,
                'title' => array(
                    'text' => ''
                ),
                'labels' => array(
                    'maxStaggerLines' => 1
                )
            ),
            'yAxis' => array(
                'title' => false,
                'gridLineWidth' => 0,
                'labels' => array(
                    // Y Axis label should never have 2 decimal places
                    'format' => str_replace('y', 'value', str_replace('2f', '0f', $format))
                    //'${value:,.0f}'
                )
            ),
            'tooltip' => array(
                //'pointFormat' => $format
                'pointFormat' => str_replace('y', 'point.y', $format)
            ),

            'series' => $series,
            'credits' => array(
                'enabled' => false
            ),
            'legend' => false,
            'plotOptions' => array(
                'series' => array(
                    'animation' => false
                )
            )
        );

        /*if ($benchmark->isPercent()) {
            $chart['yAxis']['max'] = 100;
            $chart['yAxis']['tickInterval'] = 25;
            $chart['yAxis']['labels'] = array(
                'format' => '{value}%'
            );
        }

        if ($benchmark->isDollars()) {
            $chart['yAxis']['labels'] = array(
                'format' => '${value}'
            );
        }*/

        return $chart;
    }

    public function getInstructionalActivityCosts(Observation $observation)
    {
        $reportData = array();
        $charts = array();
        $unitNames = array();
        $combinedSeries = array();
        $activityNamesForLegend = $this->getInstructionActivityCategories();

        $fields = array(
            'inst_cost_total_per_cred_hr_program_dev',
            'inst_cost_total_per_cred_hr_course_dev',
            'inst_cost_total_per_cred_hr_teaching',
            'inst_cost_total_per_cred_hr_tutoring',
            'inst_cost_total_per_cred_hr_advising',
            'inst_cost_total_per_cred_hr_ac_service',
            'inst_cost_full_per_cred_hr_assessment',
            'inst_cost_total_per_cred_hr_prof_dev',
        );

        foreach ($observation->getSubObservations() as $subObservation) {
            $unitNames[] = $subObservation->getName();
            $unitData = array($subObservation->getName());
            $unitRawData = array();

            foreach ($fields as $field) {
                $value = $subObservation->get($field);
                $benchmark = $this->getBenchmark($field);
                $formatted = $benchmark->format($value);
                $unitData[] = $formatted;
                $unitRawData[] = $value;

                // Combined chart data
                if (empty($combinedSeries[$field])) {
                    $combinedSeries[$field] = array(
                        'name' => array_shift($activityNamesForLegend),
                        'data' => array()
                    );
                }

                $combinedSeries[$field]['data'][] = $value;
            }

            // Collect the data for the table
            $reportData[] = $unitData;

            // Build the chart
            $title = $subObservation->getName() . ' Instructional Activity Cost per FTE Student';
            $series = array(
                array(
                    'data' => $unitRawData
                )
            );
            $charts[$subObservation->getName()] = $this->getBarChart(
                $benchmark,
                $this->getInstructionActivityCategories(),
                $series,
                $title,
                str_replace(array(' ', "'"), array('_', ''), $subObservation->getName())
            );
        }

        // Get combined stacked bar chart
        $combinedSeries = array_values($combinedSeries);
        $chart = $this->getBarChart(
            $benchmark,
            $unitNames,
            $combinedSeries,
            'Instructional Activity Cost per FTE Student',
            'instructional_activities_combined'
        );
        $chart['plotOptions']['series']['stacking'] = 'normal';
        $chart['legend']['enabled'] = true;
        $charts[] = $chart;

        return array($reportData, $charts);
    }

    public function getInstructionActivityCategories()
    {
        $activities = $this->getActivities();

        return array_values($activities);
    }

    public function getActivities()
    {
        return array(
            'program_dev' => 'Program Dev.',
            'course_dev' => 'Course Dev.',
            'teaching' => 'Teaching',
            'tutoring' => 'Faculty Tutoring',
            'advising' => 'Faculty Advising',
            'ac_service' => 'Academic Services',
            'assessment' => 'Assessment', // @todo: add inst_cost_full_per_cred_hr_assessment
            'prof_dev' => 'Professional Development'
        );
    }

    /**
     * Academic Unit Instructional Costs
     *
     * User selects an activity, then gets a table showing costs for the activity for each unit
     *
     * @param Observation $observation
     * @return array
     */
    public function getUnitCosts(Observation $observation)
    {
        $reportData = array();

        foreach ($this->getActivities() as $activity => $label) {
            $activityData = array(
                'name' => $label,
                'units' => array(),
            );
            $chartXCategories = array();
            $series = array();

            foreach ($observation->getSubObservations() as $subObservation) {
                $unitData = array();

                foreach ($this->getUnitCostFields() as $field) {
                    $dbColumn = $field;
                    if (substr($field, -1) == '_') {
                        $dbColumn .= $activity;

                        if (empty($series[$field])) {
                            $series[$field] = array(
                                'name' => $this->extractEmployeeTypeFromDbColumn($dbColumn),
                                'data' => array()
                            );
                        }
                    }

                    $value = $subObservation->get($dbColumn);
                    $benchmark = $this->getBenchmark($dbColumn);
                    $formatted = $benchmark->format($value);
                    $unitData[$dbColumn] = $formatted;

                    // Chart values
                    if (isset($series[$field]['data'])) {
                        $series[$field]['data'][] = $value;
                    }
                }

                $activityData['units'][$subObservation->getName()] = $unitData;
                $chartXCategories[] = $subObservation->getName();
            }


            // Chart for this activity
            $series = array_values($series);
            $activityData['chart'] = $this->getBarChart(
                $benchmark,
                $chartXCategories,
                $series,
                $label . ' Academic Unit Instructional Costs',
                'chart_' . $activity
            );
            $activityData['chart']['legend']['enabled'] = true;

            $reportData[$activity] = $activityData;
        }

        return array($reportData);
    }

    /**
     * These will get the activity key from getActivities() appended
     * @return array
     */
    protected function getUnitCostFields()
    {
        return array(
            'inst_cost_full_perc',
            'inst_cost_full_per_cred_hr_',
            'inst_cost_part_perc',
            'inst_cost_part_per_cred_hr_',
            'inst_cost_total_per_cred_hr_',
        );
    }

    protected function extractEmployeeTypeFromDbColumn($dbColumn)
    {
        $label = 'Unknown employee type';

        if (stristr($dbColumn, 'full')) {
            $label = 'Full-time';
        }
        if (stristr($dbColumn, 'part')) {
            $label = 'Part-time';
        }
        if (stristr($dbColumn, 'total')) {
            $label = 'All Faculty';
        }

        return $label;
    }

    public function getUnitDemographics(Observation $observation)
    {
        $reportData = array();
        $charts = array();
        $chartXCategories = array();

        foreach ($observation->getSubObservations() as $subObservation) {
            $unitData = array();
            $chartXCategories[] = $subObservation->getName();

            foreach ($this->getUnitDemographicsFields() as $field => $label) {
                $benchmark = $this->getBenchmark($field);
                $value = $subObservation->get($field);
                $formatted = $benchmark->format($value);

                $unitData[] = $formatted;

                // Charts
                if (!isset($charts[$field])) {
                    $charts[$field] = array(
                        'title' => $label,
                        'benchmark' => $benchmark,
                        'data' => array()
                    );
                }
                $charts[$field]['data'][] = $value;
            }

            $reportData[] = array(
                'unit' => $subObservation->getName(),
                'data' => $unitData
            );
        }

        $charts = $this->getUnitDemographicsCharts($charts, $chartXCategories);
        //pr($charts);

        return array($reportData, $charts);
    }

    public function getUnitDemographicsFields()
    {
        return array(
            'inst_cost_full_expend_per_fte_faculty' => 'Salary and Benefits Per FT Faculty Person',
            'inst_cost_part_expend_per_fte_faculty' => 'Salary and Benefits Per PT Faculty Person',
            'inst_cost_total_expend_per_fte_faculty' => 'Salary and Benefits Per Faculty Person',
            'inst_cost_fte_students' => 'Number of FTE Students',
            'inst_cost_fte_students_per_fte_faculty' => 'FTE Students Per FTE Faculty'
        );
    }

    protected function getUnitDemographicsCharts($charts, $chartXCategories)
    {
        $preparedCharts = array();
        foreach ($charts as $field => $chart) {
            $series = array(
                array(
                    'data' => $chart['data']
                )
            );

            $preparedChart = $this->getBarChart(
                $chart['benchmark'],
                $chartXCategories,
                $series
            );

            $preparedCharts[] = $preparedChart;
        }

        return $preparedCharts;
    }

    public function getStudentServicesCosts(Observation $observation)
    {
        $reportData = array();
        $chartData = array();

        foreach ($this->getStudentServicesCostsFields() as $label => $columns) {
            $activityData = array('name' => $label);

            $i = 0;
            foreach ($columns as $dbColumn) {
                if (!is_null($dbColumn)) {
                    $benchmark = $this->getBenchmark($dbColumn);
                    $value = $observation->get($dbColumn);
                    $formatted = $benchmark->format($value);
                    $activityData[$dbColumn] = $formatted;

                    $chartData[$i][$label] = $value;

                    // Hold on to a couple of benchmarks for formatting
                    if ($benchmark->isDollars()) {
                        $dollarBenchmark = $benchmark;
                    } elseif ($benchmark->getInputType() == 'float') {
                        $floatBenchmark = $benchmark;
                    }
                } else {
                    $activityData[] = $this->naPlaceholder;
                }
                $i++;
            }

            $reportData[] = $activityData;
        }

        $charts = $this->getStudentServicesCostsCharts($chartData, $dollarBenchmark, $floatBenchmark);

        return array($reportData, $charts);
    }

    protected function getStudentServicesCostsFields()
    {
        return array(
            'Admissions' => array(
                'ss_admissions_cost_per_fte_student',
                null,
                'ss_admissions_students_per_fte_emp'
            ),
            'Recruitment' => array(
                'ss_recruitment_cost_per_fte_student',
                null, 'ss_recruitment_students_per_fte_emp'
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

    protected function getStudentServicesCostsCharts($chartData, $anyDollarBenchmark, $anyFloatBenchmark)
    {
        // Rearrange data
        ksort($chartData);

        $charts = array();
        $titles = array('Cost per FTE Student', 'Cost per Student Contact', 'FTE Students per Employee');

        $benchmarks = array(
            $anyDollarBenchmark,
            $anyDollarBenchmark,
            $anyFloatBenchmark
        );

        $i = 0;
        foreach ($chartData as $data) {
            $series = array(
                array(
                    'data' => array_values($data)
                )
            );

            $chart = $this->getBarChart(
                $benchmarks[$i],
                array_keys($data),
                $series,
                $titles[$i],
                'student_services_' . $i
            );

            $charts[] = $chart;
            $i++;
        }

        return $charts;
    }

    public function getAcademicSupport(Observation $observation)
    {
        $reportData = array();
        $chartData = array();

        foreach ($this->getAcademicSupportCategories() as $label => $fields) {
            $categoryData = array();

            $i = 0;
            foreach ($fields as $dbColumn) {
                if ($observation->has($dbColumn)) {
                    $benchmark = $this->getBenchmark($dbColumn);
                    $value = $observation->get($dbColumn);
                    $formatted = $benchmark->format($value);
                    $categoryData[$dbColumn] = $formatted;

                    $chartData[$i][$label] = $value;
                } else {
                    $categoryData[] = $this->naPlaceholder;
                }

                // Hold on to a couple of benchmarks for formatting
                if ($benchmark->isDollars()) {
                    $dollarBenchmark = $benchmark;
                } elseif ($benchmark->getInputType() == 'float') {
                    $floatBenchmark = $benchmark;
                }


                $i++;
            }

            $reportData[] = array(
                'name' => $label,
                'data' => $categoryData
            );
        }

        $charts = $this->getStudentServicesCostsCharts($chartData, $dollarBenchmark, $floatBenchmark);

        return array($reportData, $charts);
    }


    protected function getAcademicSupportCategories()
    {
        return array(
            'Instructional Technology Support' => array(
                'as_tech_cost_per_fte_student',
                'as_tech_cost_per_contact',
                'as_students_per_tech_emp'
            ),
            'Library Services' => array(
                'as_library_cost_per_fte_student',
                null,
                'as_students_per_library_emp'
            ),
            'Experiential Education' => array(
                'as_experiential_cost_per_fte_student',
                'as_experiential_cost_per_contact',
                'as_students_per_experiential_emp'
            )
        );
    }

    protected function getColors()
    {
        return array(
            '#13B2CD',
            '#F5D60E',
            '#9BBE3C',
            '#00687C',
            '#FF8612',
            '#264FD5',
            '#606463',
            '#8F1AD4'
        );
    }
}
