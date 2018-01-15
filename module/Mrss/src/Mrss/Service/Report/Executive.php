<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;

class Executive extends Report
{
    protected $yourCollegeLabel = 'Your College';
    protected $seriesColors;
    protected $yourCollegeColors;
    protected $labelMedians = false;
    protected $sortPercentileCharts = false;

    public function getData()
    {
        $reportData = array();
        $important = $this->getExecutiveImportant();

        $reportData['important'] = $important;
        $reportData['strengths'] = $this->getStrengths();
        $reportData['weaknesses'] = $this->getWeaknesses();

        $reportData['intro'] = $this->getIntro();
        $reportData['moreInfo'] = $this->getMoreInfo();

        return $reportData;
    }

    protected function getIntro()
    {
        $year = $this->getObservation()->getYear();
        $memberCount = count($this->getStudy()->getSubscriptionsForYear($year));

        if ($year < 2017) {
            $intro = "<p>Your college participated in the National Community College Benchmark Project in $year.  This research is conducted annually by The National Higher Education Benchmarking Institute (NHEBI).  We would like to share some of the key results of this study with you. The report illustrates how your college compared to national data, representing $memberCount community colleges.</p>

            <p>The full NCCBP report, available online, contains more than 150 benchmarks on student demographics, measures of student success, faculty and staff data, workforce and community outreach, and institutional characteristics and effectiveness metrics.</p>

            <p>Member colleges use the benchmarks to support:
            </p><ul>
                <li>Strategic planning and selection of KPIs</li>
                <li>Accreditation</li>
                <li>Internal and external accountability activities</li>
                <li>Institutional transparency</li>
                <li>Documentation of student success</li>
            </ul>
            <p></p>";
        } else {
            $intro =
                "<p>Thank you for participating in the National Community College Benchmark Project in 2017. The report illustrates how your college compared to national data, representing 242 community colleges. The full NCCBP report, available online, contains more than 150 benchmarks, including new financial and social mobility measures.</p>";
        }

        return $intro;
    }

    protected function getMoreInfo()
    {
        $year = $this->getObservation()->getYear();

        $info = "<p>Thank you for being an NCCBP member. Find more information on the NCCBP by visiting our website <a href=\"http://nccbp.org\">NCCBP.org</a> or by calling or emailing the Benchmark Institute.</p>

            <p>Your research office will be able to provide additional benchmarks from the research, including peer comparisons.</p>

            <p>A new feature in the NCCBP reports this year is the capability for each institution to design custom reports.  This feature gives access to your institution's trend data for the years it was a member from 2007 to <?= $year ?>.</p>

            <p class=\"executiveReportLinkToThisPage\">To view reports online, go to <a href=\"http://nccbp.org/reports\">NCCBP.org/reports</a> and log in.</p>";

        if ($year >= 2017) {
            $info =
                "<p>Find more information on the NCCBP by visiting our website <a href=\"http://nccbp.org\">" .
                "NCCBP.org</a> or by calling or emailing the Benchmark Institute.</p>";
        }

        return $info;
    }

    public function getExecutiveReportConfig($year)
    {
        $config = array(
            'top-left' => array(
                'title' => 'Full-time Students Completed or Transferred in Three Years',
                'stacked' => true,
                'percent' => true,
                'benchmarks' => array(
                    'ft_perc_transf' => 'Transferred', // Transferred in 3 years, full time
                    'ft_minus4_perc_completed' => 'Completed', // Completed in 3 years, full time
                ),
                'description' => 'The percent of students out of the unduplicated full-time, first-time,
                    credit headcount from Fall ' . ($year - 4) . ' IPEDS GRS cohort who either completed a degree
                    or certificate before fall ' . ($year - 1) . ' or who transferred to four-year institutions
                    before fall ' . ($year - 1) . '.'

            ),

            'top-right' => array(
                'title' => 'Part-time Students Completed or Transferred in Six Years',
                'stacked' => true,
                'percent' => true,
                'benchmarks' => array(
                    'pt_percminus7_tran' => 'Transferred',
                    'pt_minus7_perc_completed' => 'Completed'
                ),
                'description' => 'The percent of part-time students out of the unduplicated part-time, first-time,
                 credit headcount from Fall ' . ($year - 6) . ' IPEDS GRS cohort who either completed a degree or
                 certificate before fall ' . ($year - 1) . ' or who transferred to four-year institutions before
                 fall ' . ($year - 1) . '.'
            ),

            'middle-left' => array(
                'title' => 'Persistence Rate',
                'percent' => true,
                'benchmarks' => array(
                    'next_term_pers' => 'Next-Term', // Next-term persistence
                    'fall_fall_pers' => 'Fall-Fall', // Fall-fall persistence
                ),
                'description' => 'The persistence rate is the percent of Fall ' . ($year - 2) . ' credit students,
                    both full- and part-time, who return to the campus for the next term (usually Spring ' .
                    ($year - 1) . '), or for the next fall term (Fall ' . ($year - 1) . '). This metric excludes
                     students who graduated or completed certificates in the time frame.'
            ),

            'middle-right' => array(
                'title' => 'Instructional Cost per FTE Student',
                'dollars' => true,
                'max' => 15000,
                'benchmarks' => array(
                    'cst_fte_stud' => 'Cost Per FTE Student', // Cost per FTE student
                ),
                'description' => '' . ($year - 1) . ' instructional costs include salaries, benefits, supplies,
                 travel and equipment for all full- and part-time faculty and other instructional administration
                 and support personnel per full-time equivalent student.'
            ),

            'bottom-left' => array(
                'title' => 'College-level Courses:<br>Completer Success Rate',
                'percent' => true,
                'benchmarks' => array(
                    'comp_succ' => 'Completer Success Rate', // Completer success rate
                ),
                'description' => 'The percent of students, institution-wide, who received grades of A, B, C, or
                Pass in college-level credit courses in fall ' . ($year - 2) . '.'
            ),

            'bottom-right' => array(
                'title' => "Developmental Completer <br>Success Rate",
                'percent' => true,
                'benchmarks' => array(
                    'm_comp_succ' => 'Math', // Dev math enrollee success rate
                    'w_comp_succ' => 'Writing' // Dev writing enrollee success rate
                ),
                'description' => 'The percent of students, institution-wide, who received grades of A, B, C, or
                Pass in developmental/remedial math and writing courses in fall ' . ($year - 2) . '.'
            )

        );

        if ($year >= 2017) {
            $config = $this->updateConfig($config, $year);
        }

        return $config;
    }

    protected function updateConfig($config, $year)
    {

        // Change middle right to CFI
        $config['middle-right'] = array(
            'title' => "Composite Financial Indicator",
            'percent' => false,
            'benchmarks' => array(
                'CFI' => 'CFI',
            ),
            'description' => 'The Composite Financial Index is a weighed score of the primary reserve ratio, ' .
                'net income ratio, return on net assets ratio and the viability ratio. Source: ' .
                'Strategic Financial Analysis for Higher Education: Identifying, Measuring & Reporting ' .
                'Financial Risks (Seventh Edition), by KPMG LLP; Prager, Sealy & Co., LLC; Attain LLC.'
        );

        // Move Dev Completer success rate from bottom right to bottom left
        $bottomRight = $config['bottom-right'];
        $bottomLeft = $config['bottom-left'];
        $config['bottom-left'] = $bottomRight;


        // Add social mobility to bottom right
        $config['bottom-right'] = array(
            'title' => "Social Mobility",
            'percent' => false,
            'benchmarks' => array(
                'omr_kq2up_pQ' => 'Social Mobility',
            ),
            'description' => 'The percent of students from the college that moved up two or ' .
                'more income quintiles. Source: Equality of Opportunity Project.'
        );

        // Backup measures if there's no data reported
        if (!$this->hasCFI()) {
            $config['middle-right'] = array(
                'title' => "Revenue and Expenses per FTE Student",
                'percent' => false,
                'dollars' => true,
                'benchmarks' => array(
                    'op_rev_SFTE' => 'Revenue per FTE Student',
                    'op_ex_SFTE' => 'Expenses per FTE Student',
                ),
                'description' => 'Total revenues per FTE (full-time equivalent) student and ' .
                    'Total Expenditures per FTE (full-time equivalent) student'
            );
        }

        // If they've got no social mobility data, use the old bottom left chart
        if (!$this->getObservation()->get('omr_kq2up_pQ')) {
            $config['bottom-right'] = $bottomLeft;
        }

        return $config;
    }

    /**
     * Do they have a valid CFI value? Wallace state didn't in 2017
     */
    protected function hasCFI()
    {
        $hasCFI = $this->getObservation()->get('CFI');

        // Wallace state
        if ($this->getObservation()->getId() == 4925) {
            //pr($this->getObservation()->getId());
            $hasCFI = false;
        }

        return $hasCFI;
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

        // Default format
        $format = $roundedFormat = $this->getFormat(null, 0);
        $dbColumn = '';

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
                foreach (array_keys($chartValues) as $key) {
                    if ($key == $this->yourCollegeLabel) {
                        $college = $this->getObservation()->getCollege();
                        if ($abbreviation = $college->getAbbreviation()) {
                            $key = $abbreviation;
                        }
                    }


                    $chartXCategories[] = $key;
                }
            }

            $iteration++;
        }

        list($series, $chartXCategories) = $this->sortChartData($series, $chartXCategories);

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

        // Adjust
        $year = $this->getObservation()->getYear();
        if ($year >= 2017) {
            $highChartsConfig['chart']['height'] = 370;
        }

        // Show the CFI plotline on zero only (there are negative values)
        if ($this->needsZeroLine($config)) {
            $highChartsConfig['yAxis']['plotLines'] = array(
                array(
                    'color' => '#C0D0DE',
                    'value' => 0,
                    'width' => 1,
                    'zIndex' => 99
                )
            );
            //prd($highChartsConfig);
        }

        if (isset($config['legend']) && !$config['legend']) {
            $highChartsConfig['legend'] = array('enabled' => false);
        }

        return array(
            'chart' => $highChartsConfig,
            'description' => $config['description']
        );
    }

    /**
     * @param $config
     * @return bool
     */
    protected function needsZeroLine($config)
    {
        return true;
        //return in_array('CFI', array_keys($config['benchmarks']));
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

        if (isset($config['percentiles'])) {
            $breakpoints = $config['percentiles'];
        }

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
            // Show labels for medians
            $labelEnabled = ($key == 50 && $this->labelMedians);

            $dataPoint = array(
                'name' => $label,
                'y' => floatval($value),
                'color' => $this->seriesColors[$iteration],
                'dataLabels' => array(
                    'format' => $roundedFormat,
                    'enabled' => $labelEnabled
                )
            );

            // The First bar: your college
            if ($key == $this->yourCollegeLabel) {
                // Show the value as a dataLabel for Your College
                $dataPoint['dataLabels']['enabled'] = true;
                $dataPoint['color'] = $this->yourCollegeColors[$iteration];
            }

            // Put labels at an angle for FTE rev/exp (long dollar amounts)
            if (in_array('op_rev_SFTE', array_keys($config['benchmarks']))) {
                if ($dataPoint['y']) {
                    $dataPoint['dataLabels']['rotation'] = 270;
                    $dataPoint['dataLabels']['align'] = 'left';
                    $dataPoint['dataLabels']['x'] = 0;
                    $dataPoint['dataLabels']['y'] = -3;
                }
            }

            // Not reported shouldn't get rotated and might need a line break
            if (!($dataPoint['y'])) {
                //$dataPoint['dataLabels']['rotation'] = 270;
                //$dataPoint['dataLabels']['align'] = 'left';
                //$dataPoint['dataLabels']['x'] = 2;
                //$dataPoint['dataLabels']['y'] = -70;
                //$dataPoint['dataLabels']['useHTML'] = true;
                /*$dataPoint['dataLabels']['style'] = array(
                    'fontSize' => "8px",
                    'fontWeight' => 'bold',
                    //'width' => '10px'

                );*/
            }


            // rotated
            if (!empty($config['verticalLabels'])) {
                $dataPoint['dataLabels']['rotation'] = 270;
                $dataPoint['dataLabels']['align'] = 'left';
                $dataPoint['dataLabels']['x'] = 0;
                $dataPoint['dataLabels']['y'] = -7;
            }


            // Don't show them for stacked bars (we'll show the total)
            if (!empty($config['stacked'])) {
                $dataPoint['dataLabels']['enabled'] = false;
            }

            $chartData[] = $dataPoint;
        }

        //pr($chartData);
        //$chartData = $this->sortChartData($chartData);

        $seriesItem = array(
            'name' => $config['benchmarks'][$dbColumn],
            'data' => $chartData,
            'color' => $this->seriesColors[$iteration]
        );

        return array($seriesItem, $format, $roundedFormat, $chartValues);
    }

    protected function sortChartData($series, $chartXCategories)
    {
        // Only do this if there's just one series
        if (count($series) == 1 && $this->sortPercentileCharts) {
            $points = $series[0]['data'];

            $dataOnly = array();
            foreach ($points as $point) {
                $dataOnly[] = $point['y'];
            }

            // Sort the X categories, too
            $chartValues = array_combine($chartXCategories, $dataOnly);
            asort($chartValues);
            $chartXCategories = array_keys($chartValues);

            $sortedPoints = $this->sortByY($points);


            $series[0]['data'] = $sortedPoints;
        }

        return array($series, $chartXCategories);
    }

    protected function sortByY($array)
    {
        array_multisort(array_map(function($element) {
            return $element['y'];
        }, $array), SORT_ASC, $array);

        return $array;
    }

    protected function getHighchartsConfig($config, $id, $title, $chartXCategories, $series, $format, $roundedFormat)
    {
        $type = 'column';
        if (isset($config['type'])) {
            $type = $config['type'];
        }

        $highChartsConfig = array(
            'id' => $id,
            'chart' => array(
                'type' => $type,
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
                        'allowOverlap' => true,
                        'crop' => false
                    )
                )
            )
        );

        if (!empty($config['flipData'])) {
            $highChartsConfig = $this->flipData($highChartsConfig);
        }

        if (!empty($config['stacked'])) {
            $highChartsConfig['plotOptions']['column'] = array(
                'stacking' => 'normal'
            );
        }

        if (!empty($config['percent'])) {
            $chartMax = 100;
            $maxValue = $this->getMaxValue($series);
            if ($maxValue < 50) {
                $chartMax = 50;
            }

            $highChartsConfig['yAxis']['max'] = $chartMax;
            $highChartsConfig['yAxis']['labels']['format'] = '{value}%';
            $highChartsConfig['yAxis']['tickInterval'] = 25;
        }

        if (!empty($config['dollars'])) {
            //$highChartsConfig['yAxis']['labels']['format'] =  '${value}';
            $highChartsConfig['yAxis']['labels']['formatter'] = 'formatLargeMoney';
            unset($highChartsConfig['yAxis']['labels']['format']);
        }


        return $highChartsConfig;
    }

    protected function flipData($highChartsConfig)
    {
        $oldConfig = $highChartsConfig;
        //pr($highChartsConfig);
        $oldCategories = $highChartsConfig['xAxis']['categories'];
        //pr($oldCategories);
        $newCategories = array();

        $newSeries = array();

        foreach ($oldCategories as $key => $category) {
            $newSeries[] = array(
                'name' => $this->change50ToMedian($category),
                'data' => array(),
                'color' => $this->seriesColors[$key]
            );
        }

        foreach ($highChartsConfig['series'] as $serie) {
            $newCategories[] = $this->change50ToMedian($serie['name']);

            foreach ($serie['data'] as $key => $data) {
                if ($oldCategory = $oldCategories[$key]) {
                    $newData = $data;
                    $newData['name'] = $this->change50ToMedian($oldCategory);
                    $newData['color'] = $this->seriesColors[$key];

                    $newSeries[$key]['data'][] = $newData;
                }
            }
        }

        //pr($newCategories);
        $highChartsConfig['xAxis']['categories'] = $newCategories;
        $highChartsConfig['series'] = $newSeries;

        //pr($newCategories);
        //pr($oldConfig['series']);
        //pr($highChartsConfig['series']);
        //pr($highChartsConfig);

        return $highChartsConfig;
    }

    protected function change50ToMedian($name)
    {
        if ($name == 50) {
            $name = 'Median';
        }

        return $name;
    }

    protected function getMaxValue($series)
    {
        $maxValue = 0;
        foreach ($series as $serie) {
            foreach ($serie['data'] as $dataPoint) {
                if (isset($dataPoint['y'])) {
                    $value = $dataPoint['y'];
                    if ($value > $maxValue) {
                        $maxValue = $value;
                    }
                }
            }
        }

        return $maxValue;
    }

    protected function setUpSeriesColors()
    {
        $colorConfig = $colors = getSeriesColors();

        // What color will the bar be?
        $this->seriesColors = $colorConfig['seriesColors'];
        $this->yourCollegeColors = $colorConfig['yourCollegeColors'];
    }

    public function getStrengths($weaknesses = false, $threshold = 75)
    {
        $formToExclude = $this->getStudyConfig()->form_to_exclude_from_strengths;

        $college = $this->getObservation()->getCollege();
        $year = $this->getObservation()->getYear();
        $study = $this->getStudy();

        $systemId = $system = null;
        if ($this->getStudyConfig()->use_structures) {
            $system = $this->getSystem();
            $systemId = $system->getId();
        }

        $percentileRanks = $this->getPercentileRankModel()
            ->findStrengths($college, $study, $year, $weaknesses, $formToExclude, $threshold, $systemId);


        // Filter by report structure
        if ($system) {
            $filteredRanks = array();
            $benchmarkIds = $system->getReportStructure()->getBenchmarkIdsForYear($year);
            //pr($benchmarkIds);
            foreach ($percentileRanks as $rank) {
                //pr($rank->getBenchmark()->getName());
                if (in_array($rank->getBenchmark()->getId(), $benchmarkIds)) {
                    $filteredRanks[] = $rank;
                } else {
                    //echo 'not in array:';
                    //pr($rank->getBenchmark()->getName());
                }
            }

            $percentileRanks = $filteredRanks;
        }

        $ranks = array();
        foreach ($percentileRanks as $pRank) {
            // Skip disabled benchmarks
            if (!$pRank->getBenchmark()->isAvailableForYear($year)) {
                continue;
            }


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
