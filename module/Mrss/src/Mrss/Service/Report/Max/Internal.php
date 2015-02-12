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
        $chartSeries = array();

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

    public function getBarChart(Benchmark $benchmark, $chartXCategories, $series, $title = null)
    {
        $format = $this->getFormat($benchmark);

        if (is_null($title)) {
            $title = $benchmark->getDescriptiveReportLabel();
        }

        $chart = array(
            'id' => 'chart_' . $benchmark->getDbColumn(),
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

        $fields = array(
            'inst_cost_total_per_cred_hr_program_dev',
            'inst_cost_total_per_cred_hr_course_dev',
            'inst_cost_total_per_cred_hr_teaching',
            'inst_cost_total_per_cred_hr_tutoring',
            'inst_cost_total_per_cred_hr_advising'
        );

        foreach ($observation->getSubObservations() as $subObservation) {
            $unitData = array($subObservation->getName());

            foreach ($fields as $field) {
                $value = $subObservation->get($field);
                $benchmark = $this->getBenchmark($field);
                $formatted = $benchmark->format($value);
                $unitData[] = $formatted;
            }

            $reportData[] = $unitData;
        }

        return array($reportData, null);
    }
}
