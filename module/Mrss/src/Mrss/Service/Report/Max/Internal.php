<?php

namespace Mrss\Service\Report\Max;

use Mrss\Service\Report\Max;
use Mrss\Entity\Observation;
use Mrss\Entity\Benchmark;

class Internal extends Max
{
    protected $naPlaceholder = 'N/A';

    public function getInstitutionCosts(Observation $observation)
    {
        $this->setObservation($observation);

        $pie = $this->getInstitutionCostsPieChart($observation);
        $bar = $this->getInstitutionCostsBarChart($observation);

        $charts = array($pie, $bar);

        return $charts;
    }

    public function getInstitutionCostsPieChart(Observation $observation)
    {
        $chartConfig = array(
            'title' => 'Institution Costs',
            'benchmarks' => array(
                array(
                    'dbColumn' => 'inst_full_expend',
                    'title' => 'Full-time Faculty'
                ),
                array(
                    'dbColumn' => 'inst_part_expend',
                    'title' => 'Part-time Faculty'
                ),
                array(
                    'dbColumn' => 'inst_exec_expend',
                    'title' => 'Executive Staff'
                ),
                array(
                    'dbColumn' => 'inst_admin_expend',
                    'title' => 'Clerical and Other Professional Staff'
                ),
                array(
                    'dbColumn' => 'inst_o_cost',
                    'title' => 'Non-Labor Operating Costs'
                ),
            )
        );

        $chart = $this->getPieChart($chartConfig, $observation, true);

        return $chart;
    }

    public function getInstitutionCostsBarChart(Observation $observation)
    {
        $fields = array(
            'inst_total_expend_per_fte_student' => 'All Costs',
            'inst_full_expend_per_fte_student' => 'Full-time Faculty',
            'inst_part_expend_per_fte_student' => 'Part-time Faculty',
            'inst_exec_expend_per_fte_student' => 'Executive Staff',
            'inst_admin_expend_per_fte_student' => 'Clerical Staff',
            'inst_o_cost_per_fte_student' => 'Non-Labor Operating Costs'
        );

        $chartData = array();
        $chartXCategories = array();

        foreach ($fields as $dbColumn => $label) {
            if ($observation->has($dbColumn)) {
                $benchmark = $this->getBenchmark($dbColumn);
                $value = $observation->get($dbColumn);
                //$formatted = $benchmark->format($value);
                $chartData[] = $value;
                $chartXCategories[] = $label;

            } else {
                pr($dbColumn);
            }
        }

        $series = array(
            array(
                'data' => $chartData
            )
        );

        $title = 'Costs per FTE Student';
        return $this->getBarChart($benchmark, $chartXCategories, $series, $title);
    }

    public function getPieChartColors()
    {
        return $this->getColors();
    }

    public function getInstructionalCostFields()
    {
        return array(
            'inst_cost_full_perc' => 'Percent of Full-time Faculty',
            'inst_cost_part_perc' => 'Percent of Part-time Faculty',
            'inst_cost_perc_taught_by_ft' => 'Percent of FTE Students Taught by Full-time',
            'inst_cost_perc_taught_by_pt' => 'Percent of FTE Students Taught by Part-time',
            'inst_cost_per_fte_student' => 'Faculty Salary and Benefit Costs per FTE Student',
        );
    }

    public function getInstructionalCosts(Observation $observation)
    {
        $this->setObservation($observation);

        $data = array();
        $chartXCategories = array();
        $chartData = array();


        // The institution-wide value
        //$dbColumn = 'inst_expend_per_fte_student';
        $institutionData = array(
            'label' => 'Institution',
        );
        foreach ($this->getInstructionalCostFields() as $dbColumn => $header) {
            $benchmark = $this->getBenchmark($dbColumn);
            $rawValue = $observation->get($dbColumn);
            $value = $benchmark->format($rawValue);

            $institutionData[$dbColumn] = $value;
        }
        $data[] = $institutionData;

        $collegeName = $observation->getCollege()->getName();
        $chartXCategories[] = $collegeName;
        $chartData[] = $rawValue;

        // Now the subobservations (isionisions)
        foreach ($observation->getSubObservations() as $subObservation) {
            $label = $subObservation->getName();
            $chartXCategories[] = $subObservation->getName();

            $subObData = array(
                'label' => $label
            );
            $rawValues = array();

            foreach ($this->getInstructionalCostFields() as $dbColumn => $header) {
                $benchmark = $this->getBenchmark($dbColumn);
                $rawValue = $subObservation->get($dbColumn);
                $value = $benchmark->format($rawValue);

                $subObData[$dbColumn] = $value;

                $rawValues[$dbColumn] = $rawValue;
            }

            $data[] = $subObData;
            $chartData[] = $rawValues['inst_cost_per_fte_student'];
        }

        $series = array(
            array(
                'data' => $chartData
            )
        );

        $title = 'Faculty Salary and Benefit Costs per FTE Student';
        $chart = $this->getBarChart($benchmark, $chartXCategories, $series, $title);
        //pr($chart);
        return array($data, $chart);
    }

    public function getBarChart(Benchmark $benchmark, $chartXCategories, $series, $title = null, $id = null)
    {
        $format = $this->getFormat($benchmark);
        $seriesWithDataLabels = $this->forceDataLabelsInSeries($series);
        $dataDefinition = $this->getYear() . ' ' . $this->getStudy()->getName();

        if (is_null($title)) {
            $title = $benchmark->getDescriptiveReportLabel();
        }

        if (is_null($id)) {
            $id = $benchmark->getDbColumn();
        } else {
            // No spaces or specials
            $id = preg_replace("/[^A-Za-z0-9]/", '', $id);
        }

        $chart = array(
            'id' => 'chart_' . $id,
            'chart' => array(
                'type' => 'column',
                'events' => array(
                    'load' => 'loadChart'
                ),
            ),
            'exporting' => array(
                'chartOptions' => array(
                    'series' => $seriesWithDataLabels,
                    'chart' => array(
                        'spacingBottom' => ceil(strlen($dataDefinition) / 106) * 35,
                    ),
                    'plotOptions' => array(
                        'column' => array(
                            'dataLabels' => array(
                                'enabled' => true
                            )
                        )
                    )

                ),
            ),
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
                ),
                /*'column' => array(
                    'dataLabels' => array(
                        'enabled' => true,
                        'format' => $format
                    )
                )*/
            ),
            'dataDefinition' => $dataDefinition,
            'maximizingResources' => true
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
        $this->setObservation($observation);

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

                // Convert per credit hour to per fte student (temp)
                $value = $value * 15;


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
                $subObservation->getName()
            );
        }

        if ($benchmark) {
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
        }


        return array($reportData, $charts);
    }

    public function getInstructionActivityCategories()
    {
        $activities = $this->getActivities();

        return array_values($activities);
    }

    /**
     * Academic Division Instructional Costs
     *
     * User selects an activity, then gets a table showing costs for the activity for each unit
     *
     * @param Observation $observation
     * @return array
     */
    public function getUnitCosts(Observation $observation)
    {
        $this->setObservation($observation);

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

                        // Don't chart the percentages
                        if (!in_array($field, array('inst_cost_full_', 'inst_cost_part_'))) {
                            if (empty($series[$field])) {
                                $series[$field] = array(
                                    'name' => $this->extractEmployeeTypeFromDbColumn($dbColumn),
                                    'data' => array()
                                );
                            }

                        }
                    }

                    $value = $subObservation->get($dbColumn);

                    // Temp fix convert cred hr to fte
                    if (stristr($field, 'cred_hr')) {
                        $value = $value * 15;
                    }

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


            if (!empty($benchmark)) {
                // Chart for this activity
                $series = array_values($series);
                $activityData['chart'] = $this->getBarChart(
                    $benchmark,
                    $chartXCategories,
                    $series,
                    $label . ': Academic Division Instructional Costs',
                    'chart_' . $activity
                );
                $activityData['chart']['legend']['enabled'] = true;

                $reportData[$activity] = $activityData;
            }
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
            'inst_cost_full_',
            'inst_cost_full_per_cred_hr_',
            'inst_cost_part_',
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
        $this->setObservation($observation);

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
        $this->setObservation($observation);

        $reportData = array();
        $chartData = array();

        foreach ($this->getStudentServicesCostsFields() as $label => $columns) {
            $activityData = array('name' => $label);

            $i = 0;
            foreach ($columns as $dbColumn) {
                if (!is_null($dbColumn)) {
                    $benchmark = $this->getBenchmark($dbColumn);
                    $value = $observation->get($dbColumn);

                    // Round per employee (last column)
                    $decimalPlaces = null;
                    if (stristr($dbColumn, 'per_fte_emp')) {
                        $decimalPlaces = 0;
                    }

                    $formatted = $benchmark->format($value, $decimalPlaces);

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

    protected function getStudentServicesCostsCharts(
        $chartData,
        $dollarBenchmark,
        $floatBenchmark,
        $id = 'student_services_'
    ) {
        // Rearrange data
        ksort($chartData);

        $charts = array();
        $titles = array('Cost per FTE Student', 'Cost per Student Contact', 'FTE Students per Employee');

        $benchmarks = array(
            $dollarBenchmark,
            $dollarBenchmark,
            $floatBenchmark
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
                $id . $i
            );

            $charts[] = $chart;
            $i++;
        }

        return $charts;
    }

    public function getAcademicSupport(Observation $observation)
    {
        $this->setObservation($observation);

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

        $charts = $this->getStudentServicesCostsCharts(
            $chartData,
            $dollarBenchmark,
            $floatBenchmark,
            'academic_support_'
        );

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
