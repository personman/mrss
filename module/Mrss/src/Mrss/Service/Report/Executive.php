<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;

class Executive extends Report
{
    protected $yourCollegeLabel = 'Your College';
    protected $seriesColors;
    protected $yourCollegeColors;

    public function getData()
    {
        $reportData = array();
        $important = $this->getExecutiveImportant();

        $reportData['important'] = $important;
        $reportData['strengths'] = $this->getStrengths();
        $reportData['weaknesses'] = $this->getWeaknesses();

        return $reportData;
    }

    public function getExecutiveReportConfig($year)
    {
        return array(
            array(
                'title' => 'Full-time Students Completed or Transferred in Three Years',
                'stacked' => true,
                'percent' => true,
                'benchmarks' => array(
                    'ft_perc_transf' => 'Transferred', // Transferred in 3 years, full time
                    'ft_minus4_perc_completed' => 'Completed', // Completed in 3 years, full time
                ),
                'description' => 'The percent of students out of the unduplicated full-time, first-time,
                    credit headcount from Fall ' . ($year - 4)  . ' IPEDS GRS cohort who either completed a degree
                    or certificate before fall ' . ($year - 1)  . ' or who transferred to four-year institutions
                    before fall ' . ($year - 1)  . '.'

            ),
            array(
                'title' => 'Part-time Students Completed or Transferred in Six Years',
                'stacked' => true,
                'percent' => true,
                'benchmarks' => array(
                    'pt_percminus7_tran' => 'Transferred',
                    'pt_minus7_perc_completed' => 'Completed'
                ),
                'description' => 'The percent of part-time students out of the unduplicated part-time, first-time,
                 credit headcount from Fall ' . ($year - 6)  . ' IPEDS GRS cohort who either completed a degree or
                 certificate before fall ' . ($year - 1)  . ' or who transferred to four-year institutions before
                 fall ' . ($year - 1)  . '.'
            ),
            array(
                'title' => 'Persistence Rate',
                'percent' => true,
                'benchmarks' => array(
                    'next_term_pers' => 'Next-Term', // Next-term persistence
                    'fall_fall_pers' => 'Fall-Fall', // Fall-fall persistence
                ),
                'description' => 'The persistence rate is the percent of Fall ' . ($year - 2)  . ' credit students,
                    both full- and part-time, who return to the campus for the next term (usually Spring ' .
                    ($year - 1) . '), or for the next fall term (Fall ' . ($year - 1)  . '). This metric excludes
                     students who graduated or completed certificates in the time frame.'
            ),
            array(
                'title' => 'Instructional Cost per FTE Student',
                'dollars' => true,
                'max' => 15000,
                'benchmarks' => array(
                    'cst_fte_stud' => 'Cost Per FTE Student', // Cost per FTE student
                ),
                'description' => '' . ($year - 1)  . ' instructional costs include salaries, benefits, supplies,
                 travel and equipment for all full- and part-time faculty and other instructional administration
                 and support personnel per full-time equivalent student.'
            ),
            array(
                'title' => 'College-level Courses:<br>Completer Success Rate',
                'percent' => true,
                'benchmarks' => array(
                    'comp_succ' => 'Completer Success Rate', // Completer success rate
                ),
                'description' => 'The percent of students, institution-wide, who received grades of A, B, C, or
                Pass in college-level credit courses in fall ' . ($year - 2)  . '.'
            ),
            array(
                'title' => "Developmental Completer <br>Success Rate",
                'percent' => true,
                'benchmarks' => array(
                    'm_comp_succ' => 'Math', // Dev math enrollee success rate
                    'w_comp_succ' => 'Writing' // Dev writing enrollee success rate
                ),
                'description' => 'The percent of students, institution-wide, who received grades of A, B, C, or
                Pass in developmental/remedial math and writing courses in fall ' . ($year - 2)  . '.'
            )

        );
    }

    public function getExecutiveImportant()
    {
        $importantCharts = array();

        $config = $this->getExecutiveReportConfig(
            $this->getObservation()->getYear()
        );

        foreach ($config as $importantConfig) {
            $importantCharts[] = $this
                ->getExecutiveBarChart($importantConfig);
        }

        return $importantCharts;
    }

    public function getExecutiveBarChart($config)
    {
        $chartXCategories = array();

        $this->setUpSeriesColors();


        $series = array();
        $iteration = 0;
        foreach ($config['benchmarks'] as $dbColumn => $label) {
            list($seriesItem, $format, $roundedFormat, $chartValues) = $this->buildBarChart(
                $config,
                $dbColumn,
                $label,
                $iteration
            );
            $series[] = $seriesItem;

            // Set up the categories
            if (empty($chartXCategories)) {
                foreach ($chartValues as $key => $chartValue) {
                    $chartXCategories[] = $key;
                }
            }

            $iteration++;

        }

        $chartTitle = $config['title'];

        $highChartsConfig = $this->getHighchartsConfig(
            $config,
            $dbColumn,
            $chartTitle,
            $chartXCategories,
            $series,
            $format,
            $roundedFormat
        );

        return array(
            'chart' => $highChartsConfig,
            'description' => $config['description']
        );
    }

    protected function buildBarChart($config, $dbColumn, $label, $iteration)
    {
        $benchmark = $this->getBenchmarkModel()
            ->findOneByDbColumnAndStudy($dbColumn, $this->getStudy()->getId());

        // Get the college's reported value
        $reportedValue = $this->getObservation()->get($dbColumn);

        $format = $this->getFormat($benchmark);
        $roundedFormat = $this->getFormat($benchmark, 0);

        $chartValues = array($this->yourCollegeLabel => $reportedValue);

        // Load the percentiles
        $breakpoints = $this->getPercentileBreakpointsForStudy();
        $percentiles = $this->getPercentileModel()
            ->findByBenchmarkAndYear($benchmark, $this->getYear(), $breakpoints);

        $percentileData = array();
        foreach ($percentiles as $percentile) {
            $percentileData[$percentile->getPercentile()] =
                floatval($percentile->getValue());
        }
        unset($percentileData['N']);

        $chartValues = $chartValues + $percentileData;

        $chartData = array();

        foreach ($chartValues as $key => $value) {
            $dataPoint = array(
                'name' => $label,
                'y' => floatval($value),
                'color' => $this->seriesColors[$iteration],
                'dataLabels' => array(
                    'format' => $roundedFormat,
                    'enabled' => false
                )
            );

            // The First bar: your college
            if ($key == $this->yourCollegeLabel) {
                // Show the value as a dataLabel for Your College
                $dataPoint['dataLabels']['enabled'] = true;
                $dataPoint['color'] = $this->yourCollegeColors[$iteration];

                // Don't show them for stacked bars (we'll show the total)
                if (!empty($config['stacked'])) {
                    $dataPoint['dataLabels']['enabled'] = false;
                }
            }

            $chartData[] = $dataPoint;
        }

        $seriesItem = array(
            'name' => $config['benchmarks'][$dbColumn],
            'data' => $chartData,
            'color' => $this->seriesColors[$iteration]
        );

        return array($seriesItem, $format, $roundedFormat, $chartValues);
    }

    protected function getHighchartsConfig($config, $id, $title, $chartXCategories, $series, $format, $roundedFormat)
    {
        $highChartsConfig = array(
            'id' => $id,
            'chart' => array(
                'type' => 'column',
                'events' => array(
                    'load' => 'chartLoaded'
                )
            ),
            'title' => array(
                'text' => $title,
                'style' => array(
                    'color' => '#336699'
                )
            ),
            'xAxis' => array(
                'categories' => $chartXCategories,
                'tickLength' => 0,
                'title' => array(
                    'text' => 'Percentiles',
                    'style' => array(
                        'color' => '#336699'
                    )
                )
            ),
            'yAxis' => array(
                'title' => false,
                'gridLineWidth' => 0,
                'stackLabels' => array(
                    'enabled' => true,
                    'format' => str_replace('y', 'total', $roundedFormat)
                ),
                'labels' => array(
                    'format' => str_replace('y', 'value', $format)
                )
            ),
            'tooltip' => array(
                'pointFormat' => str_replace('y', 'point.y', $format)
            ),
            'series' => $series,
            'credits' => array(
                'enabled' => false
            ),
            'plotOptions' => array(
                'series' => array(
                    'animation' => false,
                    'dataLabels' => array(
                        'overflow' => 'none',
                        'crop' => false
                    )
                )
            )
        );

        if (!empty($config['stacked'])) {
            $highChartsConfig['plotOptions']['column'] = array(
                'stacking' => 'normal'
            );
        }

        if (!empty($config['percent'])) {
            $highChartsConfig['yAxis']['max'] = 100;
            $highChartsConfig['yAxis']['labels']['format'] = '{value}%';
            $highChartsConfig['yAxis']['tickInterval'] = 25;
        }

        if (!empty($config['dollars'])) {
            $highChartsConfig['yAxis']['labels']['format'] =  '${value}';
        }

        return $highChartsConfig;
    }

    protected function setUpSeriesColors()
    {
        $colorConfig = $colors = array(
            'seriesColors' => array(
                '#9cc03e', // '#005595' lightened 40%
                '#3366B4', // '#519548' lightened 30%
            ),
            'yourCollegeColors' => array(
                '#507400',
                '#001A68'
            )
        );
        // What color will the bar be?
        $this->seriesColors = $colorConfig['seriesColors'];
        $this->yourCollegeColors = $colorConfig['yourCollegeColors'];
    }

    public function getStrengths($weaknesses = false, $threshold = 75)
    {
        $college = $this->getObservation()->getCollege();
        $year = $this->getObservation()->getYear();
        $study = $this->getStudy();

        $percentileRanks = $this->getPercentileRankModel()
            ->findStrengths($college, $study, $year, $weaknesses, 1, $threshold);



        $ranks = array();
        foreach ($percentileRanks as $pRank) {
            $name = $pRank->getBenchmark()->getDescriptiveReportLabel();
            $append = '';
            if (!$pRank->getBenchmark()->getHighIsBetter()) {
                $append = '<span class="execAppend">(Low is better)</span>';
            }

            $ranks[] = array(
                'name' => $name,
                'dbColumn' => $pRank->getBenchmark()->getDbColumn(),
                'form_url' => $pRank->getBenchmark()->getBenchmarkGroup()->getUrl(),
                'rank' => $this->getOrdinal($pRank->getRank()),
                'benchmark_id' => $pRank->getBenchmark()->getId(),
                'append' => $append
            );
        }

        return $ranks;
    }

    public function getWeaknesses($threshold = 75)
    {
        return $this->getStrengths(true, $threshold);
    }
}
