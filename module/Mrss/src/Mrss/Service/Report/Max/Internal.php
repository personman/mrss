<?php

namespace Mrss\Service\Report\Max;

use Mrss\Service\Report;
use Mrss\Entity\Observation;
use Mrss\Entity\Benchmark;

class Internal extends Report
{
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
                    'format' => str_replace('y', 'value', $format)
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
            'inst_cost_total_per_cred_hr_advising'
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
            $title = $subObservation->getName() . ' Instructional Activity Costs';
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
            'Instructional Activity Costs',
            'instructional_activities_combined'
        );
        $chart['plotOptions']['series']['stacking'] = 'normal';
        $chart['legend']['enabled'] = true;
        $charts[] = $chart;

        return array($reportData, $charts);
    }

    public function getInstructionActivityCategories()
    {
        return array(
            'Program Dev.',
            'Course Dev.',
            'Teaching',
            'Faculty Tutoring',
            'Faculty Advising'
        );
    }
}
