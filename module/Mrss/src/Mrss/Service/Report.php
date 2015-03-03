<?php

namespace Mrss\Service;

use Mrss\Entity\Study;
use Mrss\Entity\Benchmark;
use Mrss\Entity\College;
use Mrss\Entity\Observation;
use Mrss\Service\Report\Calculator;
use Mrss\Service\ComputedFields;
use Zend\Mail\Transport\Smtp;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_Shared_Font;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Log\Formatter\Simple;

class Report
{
    /**
     * @var Study
     */
    protected $study;

    /**
     * @var array
     */
    protected $subscriptions = array();

    /**
     * @var Calculator
     */
    protected $calculator;

    /**
     * @var ComputedFields
     */
    protected $computedFieldsService;

    /**
     * @var VariableSubstitution
     */
    protected $variableSubstitution;

    /**
     * @var \Mrss\Model\Subscription
     */
    protected $subscriptionModel;

    /**
     * @var \Mrss\Model\Benchmark
     */
    protected $benchmarkModel;

    /**
     * @var \Mrss\Model\College
     */
    protected $collegeModel;

    /**
     * @var \Mrss\Model\Percentile
     */
    protected $percentileModel;

    /**
     * @var \Mrss\Model\PercentileRank
     */
    protected $percentileRankModel;

    /**
     * @var \Mrss\Model\Setting
     */
    protected $settingModel;

    /**
     * @var \Mrss\Model\Outlier
     */
    protected $outlierModel;

    /**
     * @var \Mrss\Model\System
     */
    protected $systemModel;

    /**
     * @var \Mrss\Entity\Observation
     */
    protected $observation;

    /**
     * @var Smtp
     */
    protected $mailTransport;

    protected $debug = false;
    
    protected $start;
    
    public function __construct()
    {
        $this->start = microtime(true);
    }

    public function getYearsWithSubscriptions()
    {
        $years = $this->getSubscriptionModel()
            ->getYearsWithSubscriptions($this->getStudy());

        return $years;
    }

    public function getCalculationInfo()
    {
        $years = $this->getYearsWithSubscriptions();

        // Also show the date the report was calculated
        $yearsWithCalculationDates = array();
        foreach ($years as $year) {
            $yearsWithCalculationDates[$year] = array();

            $key = $this->getReportCalculatedSettingKey($year);
            $yearsWithCalculationDates[$year]['report'] = $this->getSettingModel()
                ->getValueForIdentifier($key);

            $key = $this->getReportCalculatedSettingKey($year, true);
            $yearsWithCalculationDates[$year]['system'] = $this->getSettingModel()
                ->getValueForIdentifier($key);

            $key = $this->getOutliersCalculatedSettingKey($year);
            $yearsWithCalculationDates[$year]['outliers'] = $this->getSettingModel()
                ->getValueForIdentifier($key);
        }

        return $yearsWithCalculationDates;
    }

    /**
     * Build a unique key for the year and study
     *
     * @param $year
     * @param bool $systems
     * @return string
     */
    public function getReportCalculatedSettingKey($year, $systems = false)
    {
        $studyId = $this->getStudy()->getId();

        $key = "report_calculated_{$studyId}_$year";

        if ($systems) {
            $key = 'system_' . $key;
        }

        return $key;
    }

    /**
     * Build a unique key for the year and study
     *
     * @param $year
     * @return string
     */
    public function getOutliersCalculatedSettingKey($year)
    {
        $studyId = $this->getStudy()->getId();

        $key = "outliers_calculated_{$studyId}_$year";

        return $key;
    }

    public function collectDataForBenchmark(
        Benchmark $benchmark,
        $year,
        $skipNull = true,
        $system = null
    ) {
        $subscriptions = $this->getSubscriptions($year, $system);
        //prd(count($subscriptions));

        $data = array();
        $iData = array();
        $skipped = 0;
        /** @var $subscription /Mrss/Entity/Subscription */
        foreach ($subscriptions as $subscription) {
            /** @var /Mrss/Entity/Observation $observation */
            if ($observation = $subscription->getObservation()) {
                $dbColumn = $benchmark->getDbColumn();
                $value = $observation->get($dbColumn);
                $collegeId = $subscription->getCollege()->getId();
                if (!empty($_GET['debug'])
                    && $benchmark->getDbColumn() == $_GET['debug']) {
                    //pr($value);
                }
                // Leave out null values
                if ($skipNull && $value === null) {
                    $skipped++;
                    continue;
                }

                $data[$collegeId] = $value;
                $ipeds = $subscription->getCollege()->getIpeds();
                $iData[$ipeds] = $value;
            }
        }

        ksort($iData);
        //if ($benchmark->getDbColumn() == 'ft_perc_comp') {
        if (!empty($_GET['debug']) && $benchmark->getDbColumn() == $_GET['debug']) {
            if (empty($system) ||
                (!empty($_GET['system']) && $_GET['system'] == $system->getId())) {
                pr(count($subscriptions));
                pr(count($iData));
                pr($year);
                echo 'skipped:';
                pr($skipped);
                prd($iData);
            }
        }
        //echo $benchmark->getName();
        //echo implode('<br>', $iData); die('ok');
        //prd($iData);

        return $data;
    }

    /**
     * Most reported values should be rounded to 0 decimal places.
     * These are the exceptions
     *
     * @param Benchmark $benchmark
     * @return int
     */
    public function getDecimalPlaces(Benchmark $benchmark)
    {
        $dbColumn = $benchmark->getDbColumn();

        $map = array(
            'enrollment_information_contact_hours_per_student' => 1,
            'enrollment_information_market_penetration' => 1,
            'fst_yr_gpa' => 2,
            'avrg_1y_crh' => 2,
            'n96_exp' => 1,
            'n97_ova_exp' => 1,
            'n98_enr_again' => 1,
            'ac_adv_coun' => 1,
            'ac_serv' => 1,
            'adm_fin_aid' => 1,
            'camp_clim' => 1,
            'camp_supp' => 1,
            'conc_indiv' => 1,
            'instr_eff' => 1,
            'reg_eff' => 1,
            'resp_div_pop' => 1,
            'safe_sec' => 1,
            'serv_exc' => 1,
            'stud_centr' => 1,
            'act_coll_learn' => 1,
            'stud_eff' => 1,
            'acad_chall' => 1,
            'stud_fac_int' => 1,
            'sup_learn' => 1,
            'choo_again' => 1,
            'ova_impr' => 1,
            'av_cred_sec_size' => 2,
            'griev_occ_rate' => 4,
            'harass_occ_rate' => 4,
            'stu_fac_ratio' => 2,
            'stud_inst_serv_ratio' => 2,
            'empl_inst_serv_ratio' => 2
        );

        $decimalPlaces = 0;
        if (isset($map[$dbColumn])) {
            $decimalPlaces = $map[$dbColumn];
        } elseif ($benchmark->getInputType() == 'float') {
            $decimalPlaces = 2;
        } else {
            //All NCCBP percentages should use 2 decimal places
            if ($this->getStudy()->getId() == 1 && $benchmark->isPercent()) {
                $decimalPlaces = 2;
            }
        }

        return $decimalPlaces;
    }

    public function downloadExcel($excel, $filename)
    {
        header(
            'Content-Type: '.
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');

        die;
    }

    /**
     * Executive summary report showing charts for key benchmarks
     *
     * @param Observation $observation
     * @throws \Exception
     * @return bool
     */
    public function getSummaryReportData(Observation $observation)
    {
        $this->setObservation($observation);

        $config = $this->getSummaryReportConfig();
        $reportData = array();

        foreach ($config as $section) {
            $sectionData = array(
                'name' => $section['name'],
                'charts' => array()
            );

            foreach ($section['charts'] as $chartConfig) {
                $type = 'percentileBarChart';
                if (!empty($chartConfig['type'])) {
                    $type = $chartConfig['type'];
                }

                if ($type == 'percentileBarChart') {
                    $chart = $this->getPercentileBarChart($chartConfig, $observation);
                } elseif ($type == 'pieChart') {
                    $chart = $this->getPieChart($chartConfig, $observation);
                } else {
                    throw new \Exception('Unknown chart type');
                }

                if (empty($chartConfig['description'])) {
                    $chartConfig['description'] = '';
                }

                $sectionData['charts'][] = array(
                    'chart' => $chart,
                    'description' => $chartConfig['description']
                );
            }

            $reportData[] = $sectionData;
        }

        return $reportData;
    }

    public function getPercentileBarChart($config, Observation $observation)
    {
        $dbColumn = $config['dbColumn'];
        $benchmark = $this->getBenchmarkModel()->findOneByDbColumnAndStudy($dbColumn, $this->getStudy()->getId());

        if (empty($benchmark)) {
            return false;
        }

        $percentiles = $this->getPercentileModel()
            ->findByBenchmarkAndYear($benchmark, $observation->getYear());
        $percentileData = array();
        foreach ($percentiles as /** var Percentile */ $percentile) {
            $percentileData[$percentile->getPercentile()] = $percentile
                ->getValue();
        }

        $chart = $this->getPercentileChartConfig(
            $benchmark,
            $percentileData,
            $observation->get($dbColumn),
            $config
        );

        return $chart;
    }

    public function getPieChart($chartConfig, Observation $observation, $usePercentage = false)
    {

        if ($usePercentage) {
            $total = 0;
            foreach ($chartConfig['benchmarks'] as $i => $benchmark) {
                $total += $this->getPieChartValue($benchmark, $observation);
            }
        }

        $colors = $this->getPieChartColors();
        $data = array();
        foreach ($chartConfig['benchmarks'] as $i => $benchmark) {
            // National median or college's reported value?
            $value = $this->getPieChartValue($benchmark, $observation);

            // Skip zero values
            if (empty($value)) {
                continue;
            }

            if ($usePercentage) {
                $value = round($value / $total * 100, 1);
            }

            $title = $benchmark['title'];

            $data[] = array(
                'name' => $title,
                'y' => $value,
                'color' => $colors[$i],
            );
        }

        $series = array(
            array(
                'name' => 'Value',
                'data' => $data
            )
        );

        $dataDefinition = $this->getYear() . ' ' . $this->getStudy()->getName();

        $chart = array(
            'id' => 'chart_' . uniqid(),
            'chart' => array(
                'type' => 'pie',
                'events' => array(
                    'load' => 'loadChart'
                ),

            ),
            'exporting' => array(
                'chartOptions' => array(
                    //'series' => $seriesWithDataLabels,
                    'chart' => array(
                        'spacingBottom' => ceil(strlen($dataDefinition) / 106) * 35,
                    ),
                ),
            ),
            'title' => array(
                'text' => $chartConfig['title'],
            ),
            'series' => $series,
            'credits' => array(
                'enabled' => false
            ),
            'dataDefinition' => $dataDefinition
        );

        if ($usePercentage) {
            $chart['tooltip'] = array('valueSuffix' => '%');
        }

        return $chart;
    }

    public function getPieChartValue($benchmarkInfo, Observation $observation)
    {
        $value = null;

        if (!empty($benchmarkInfo['median'])) {
            $benchmarkEntity = $this->getBenchmarkModel()->findOneByDbColumn(
                $benchmarkInfo['dbColumn']
            );

            $value = $this->getPercentileModel()
                ->findByBenchmarkYearAndPercentile(
                    $benchmarkEntity->getId(),
                    $observation->getYear(),
                    50
                )->getValue();
        } else {
            $value = $observation->get($benchmarkInfo['dbColumn']);
        }

        return $value;
    }

    public function getBubbleChart($x, $y, $size, $title)
    {
        $study = $this->getStudy();

        $subscriptions = $this->getSubscriptionModel()
            ->findByStudyAndYear($study->getId(), $study->getCurrentYear() - 1);

        $data = array();
        foreach ($subscriptions as $subscription) {
            $observation = $subscription->getObservation();

            $xVal = $observation->get($x);
            $yVal = $observation->get($y);
            $sizeVal = $observation->get($size);

            if ($xVal && $yVal && $sizeVal) {
                $data[] = array(
                    floatval($xVal),
                    floatval($yVal),
                    floatval($sizeVal)
                );
            }
        }

        $xLabel = $this->getBenchmarkModel()->findOneByDbColumn($x)->getName();
        $yLabel = $this->getBenchmarkModel()->findOneByDbColumn($y)->getName();


        $series = array(
            array(
                'name' => 'Institutions',
                'data' => $data
            )
        );

        if (empty($title)) {
            $title = 'Test Chart';
        }


        $chart = array(
            'id' => 'chart_' . uniqid(),
            'chart' => array(
                'type' => 'bubble',
                'zoomType' => 'xy'
            ),
            'title' => array(
                'text' => $title,
            ),
            'xAxis' => array(
                'title' => array(
                    'enabled' => true,
                    'text' => $xLabel
                )
            ),
            'yAxis' => array(
                'title' => array(
                    'enabled' => true,
                    'text' => $yLabel
                )
            ),
            'exporting' => array(
                'enabled' => true
            ),
            'credits' => array(
                'enabled' => false
            ),
            'series' => $series
        );

        return $chart;

    }

    public function getPieChartColors()
    {
        return array(
            '#002C57',
            '#0065A1',
            '#92B1CB',
            '#DDDDDD',
            '#AAAAAA',
            '#888'
        );
    }

    public function getSummaryReportConfig()
    {
        $studyId = $this->getStudy()->getId();

        // The lines were too long, so move this to a config file
        $configFile = dirname(dirname(dirname(dirname(dirname(__DIR__))))) .
        '/config/summary.report.config.php';

        $configs = include($configFile);

        return $configs[$studyId];
    }

    /**
     * Return an array suitable for passing right into highcharts
     *
     * @param Benchmark $benchmark
     * @param $percentileData
     * @param $reportedValue
     * @param $chartConfig
     * @return array
     */
    public function getPercentileChartConfig(
        Benchmark $benchmark,
        $percentileData,
        $reportedValue,
        $chartConfig
    ) {
        if (empty($chartConfig['title'])) {
            $chartConfig['title'] = $this->getVariableSubstitution()
                ->substitute($benchmark->getDescriptiveReportLabel());
        }

        unset($percentileData['N']);

        $chartXCategories =$this->getPercentileBreakPointLabels();
        $chartValues = $percentileData;

        // Only add Your College to the chart if the reported value is not null
        if (!is_null($reportedValue)) {
            $chartXCategories = array_merge(
                array($this->getYourCollegeLabel()),
                $chartXCategories
            );

            $chartValues = array_merge(
                array($this->getYourCollegeLabel() => floatval($reportedValue)),
                $chartValues
            );
        }


        $format = $this->getFormat($benchmark);

        // Put the college's data in its place
        if (count($chartValues) != count($chartXCategories)) {
            $dbForLog = $benchmark->getDbColumn();
            $collegeForLog = $this->getObservation()->getCollege()->getName();
            $logYear = $this->getObservation()->getYear();
            $this->getErrorLog()->warn(
                "Mismatched chart labels/data for $dbForLog, college: $collegeForLog, year: $logYear."
            );
        }
        $chartValues = array_combine($chartXCategories, $chartValues);
        asort($chartValues);
        $chartXCategories = array_keys($chartValues);

        if (isset($chartConfig['decimal_places'])) {
            $roundTo = $chartConfig['decimal_places'];
        } else {
            $roundTo = $this->getDecimalPlaces($benchmark);
        }

        if (false && $benchmark->getDbColumn() == 'hs_stud_hdct') {
            $r = $benchmark->isPercent();
            pr($r);
            pr($benchmark->getId());
            pr($benchmark->getInputType());
            pr($benchmark->getDbColumn());
            prd($roundTo);
        }

        $chartData = array();
        foreach ($chartValues as $i => $value) {
            $value = round($value, $roundTo);

            if (!empty($chartXCategories[$i])) {
                $label = $chartXCategories[$i];
            } else {
                $label = $i;
            }

            // Your college
            if ($i === $this->getYourCollegeLabel()) {
                $dataLabelEnabled = true;
                $color = '#002C57';
            } else {
                $dataLabelEnabled = false;
                $color = '#0065A1';
            }

            $chartData[] = array(
                'name' => $label,
                'y' => $value,
                'color' => $color,
                'dataLabels' => array(
                    'enabled' => $dataLabelEnabled,
                    'crop' => false,
                    'overflow' => 'none',
                    'format' => $format
                )
            );
        }

        $series = array(
            array(
                'name' => 'Value',
                'data' => $chartData
            )
        );


        //$seriesWithDataLabels = $this->forceDataLabelsInSeries($series);
        $dataDefinition = $this->getChartFooter($benchmark);

        $chart = array(
            'id' => 'chart_' . $benchmark->getDbColumn(),
            'chart' => array(
                'type' => 'column',
                'events' => array(
                    'load' => 'loadChart'
                ),
            ),
            'exporting' => array(
                'chartOptions' => array(
                    //'series' => $seriesWithDataLabels,
                    'chart' => array(
                        'spacingBottom' => ceil(strlen($dataDefinition) / 106) * 35,
                    ),
                ),
            ),
            'title' => array(
                'text' => $chartConfig['title'],
            ),
            'xAxis' => array(
                'categories' => $chartXCategories,
                'tickLength' => 0,
                'title' => array(
                    'text' => 'Percentiles'
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
            ),
            'dataDefinition' => $dataDefinition,
        );

        if ($benchmark->isPercent()) {
            $chart['yAxis']['max'] = 100;
            $chart['yAxis']['tickInterval'] = 25;
            $chart['yAxis']['labels'] = array(
                'format' => '{value}%'
            );
        }

        // Noel-Levitz max value is 6
        if ($this->isNoelLevitz($benchmark->getDbColumn())) {
            $chart['yAxis']['max'] = 6;
        }

        if ($benchmark->isDollars()) {
            $chart['yAxis']['labels'] = array(
                'format' => '${value}'
            );
        }

        //var_dump($chartConfig);
        //var_dump($chart);
        return $chart;
    }

    protected function isNoelLevitz($dbColumn)
    {
        $NLFields = array(
            'n96_exp',
            'n97_ova_exp',
            'ac_adv_coun',
            'ac_serv',
            'adm_fin_aid',
            'camp_clim',
            'camp_supp',
            'conc_indiv',
            'instr_eff',
            'reg_eff',
            'resp_div_pop',
            'safe_sec',
            'serv_exc',
            'serv_exc'
        );

        return in_array($dbColumn, $NLFields);
    }

    public function forceDataLabelsInSeries($series)
    {
        $seriesWithDataLabels = array();

        foreach ($series as $dataSet) {
            $chartData = $dataSet['data'];
            $chartDataWithLabels = array();
            foreach ($chartData as $point) {
                $point['dataLabels']['enabled'] = true;
                $chartDataWithLabels[] = $point;
            }

            $dataSet['data'] = $chartDataWithLabels;
            $seriesWithDataLabels[] = $dataSet;
        }

        return $seriesWithDataLabels;
    }

    public function getFormat(Benchmark $benchmark, $forceDecimalPlaces = null)
    {
        if ($forceDecimalPlaces !== null) {
            $decimalPlaces = $forceDecimalPlaces;
        } else {
            $decimalPlaces = $this->getDecimalPlaces($benchmark);
        }

        $numberFormat = ',.' . $decimalPlaces . 'f';
        $format = "{y:$numberFormat}";

        if ($benchmark->isPercent()) {
            $format = "{y:$numberFormat}%";
        } elseif ($benchmark->isDollars()) {
            $format = "\${y:$numberFormat}";
        }

        return $format;
    }

    public function getPercentileBreakpoints()
    {
        return array(10, 25, 50, 75, 90);
    }

    public function getPercentileLabel($label)
    {
        if ($label != $this->getYourCollegeLabel()) {
            // Rename 50th percentile to 'median'
            if ($label == '50') {
                $label = 'Median';
            } else {

                $label = $label . 'th';
            }
        }

        return $label;
    }

    public function getYourCollegeLabel()
    {
        return 'Your College';
    }

    public function getPercentileBreakPointLabels()
    {
        $breakpoints = $this->getPercentileBreakpoints();
        $labels = array();
        foreach ($breakpoints as $breakpoint) {
            $label = $this->getOrdinal($breakpoint);

            $labels[] = $label;
        }

        return $labels;
    }

    public function getOrdinal($number)
    {
        // We don't want to show 0 or 99, so use > or < for those
        if ($number < 1) {
            $html = '<1<sup>st</sup>';
        } elseif ($number > 99) {
            $html = '>99<sup>th</sup>';
        } else {
            $rounded = round($number);

            $ends = array('th','st','nd','rd','th','th','th','th','th','th');
            if (($rounded % 100) >= 11 && ($rounded % 100) <= 13) {
                $abbreviation = 'th';
            } else {
                $abbreviation = $ends[$rounded % 10];
            }

            $html = "$rounded<sup>$abbreviation</sup>";
        }

        return $html;
    }

    public function getTrends($dbColumn, $colleges)
    {
        $report = array();
        $i = 1;
        foreach ($colleges as $collegeId) {
            $college = $this->getCollegeModel()->find($collegeId);

            $values = array();
            foreach ($college->getObservations() as $observation) {
                $value = $observation->get($dbColumn);
                $value = round($value, 2);
                $values[$observation->getYear()] = $value;
            }

            $label = "College " . $this->numberToLetter($i);

            $report[$label] = $values;

            $i++;
        }

        return $report;
    }

    /**
     * Takes a number and converts it to a-z,aa-zz,aaa-zzz, etc with uppercase option
     *
     * @param int number to convert
     * @param bool $uppercase Uppercase?
     * @return string letters from number input
     */

    public function numberToLetter($num, $uppercase = true)
    {
        $num -= 1;

        $letter = 	chr(($num % 26) + 97);
        $letter .= 	(floor($num/26) > 0) ? str_repeat($letter, floor($num/26)) : '';
        return 		($uppercase ? strtoupper($letter) : $letter);
    }

    public function setStudy(Study $study)
    {
        $this->study = $study;

        return $this;
    }

    public function getStudy()
    {
        return $this->study;
    }

    public function setComputedFieldsService(ComputedFields $service)
    {
        $this->computedFieldsService = $service;

        return $this;
    }

    public function getComputedFieldsService()
    {
        return $this->computedFieldsService;
    }

    public function setSubscriptionModel($model)
    {
        $this->subscriptionModel = $model;

        return $this;
    }

    public function getSubscriptionModel()
    {
        return $this->subscriptionModel;
    }

    public function setBenchmarkModel($model)
    {
        $this->benchmarkModel = $model;

        return $this;
    }

    public function getBenchmarkModel()
    {
        return $this->benchmarkModel;
    }

    public function setCollegeModel($model)
    {
        $this->collegeModel = $model;

        return $this;
    }

    public function getCollegeModel()
    {
        return $this->collegeModel;
    }

    public function setPercentileModel($model)
    {
        $this->percentileModel = $model;

        return $this;
    }

    public function getPercentileModel()
    {
        return $this->percentileModel;
    }

    public function setPercentileRankModel($model)
    {
        $this->percentileRankModel = $model;

        return $this;
    }

    /**
     * @return \Mrss\Model\PercentileRank
     */
    public function getPercentileRankModel()
    {
        return $this->percentileRankModel;
    }

    public function setSettingModel($model)
    {
        $this->settingModel = $model;

        return $this;
    }

    public function getSettingModel()
    {
        return $this->settingModel;
    }

    public function setOutlierModel($model)
    {
        $this->outlierModel = $model;

        return $this;
    }

    public function getOutlierModel()
    {
        return $this->outlierModel;
    }

    public function setSystemModel($model)
    {
        $this->systemModel = $model;

        return $this;
    }

    /**
     * @return \Mrss\Model\System
     */
    public function getSystemModel()
    {
        return $this->systemModel;
    }


    public function setCalculator(Calculator $calculator)
    {
        $this->calculator = $calculator;

        return $this;
    }

    public function getCalculator()
    {
        return $this->calculator;
    }

    public function setVariableSubstitution(VariableSubstitution $service)
    {
        $this->variableSubstitution = $service;

        return $this;
    }

    public function getVariableSubstitution()
    {
        return $this->variableSubstitution;
    }

    public function setMailTransport(Smtp $transport)
    {
        $this->mailTransport = $transport;

        return $this;
    }

    public function getMailTransport()
    {
        return $this->mailTransport;
    }

    protected function debug($variable)
    {
        if ($this->debug) {
            pr($variable);
        }
    }

    protected function debugTimer($message = null)
    {
        if ($this->debug) {
            
            $elapsed = round(microtime(1) - $this->start, 3);
            $message = $elapsed . "s: " . $message;
            $this->debug($message);
        }
    }

    /**
     * @param $year
     * @param \Mrss\Entity\System $system
     * @return \Mrss\Entity\Subscription[]
     */
    protected function getSubscriptions($year, $system = null)
    {
        $key = $year;

        if ($system) {
            $key = $year . '_' . $system->getId();
        }

        if (!isset($this->subscriptions[$key])) {
            $this->debugTimer("Starting to collect subscriptions. ");
            if ($system) {
                $study = $this->getStudy();

                $subscriptions = array();
                foreach ($system->getColleges() as $college) {
                    $sub = $college->getSubscriptionByStudyAndYear(
                        $study->getId(),
                        $year
                    );

                    if ($sub) {
                        $subscriptions[] = $sub;
                    } else {

                    }

                    /*foreach ($college->getSubscriptionsForStudy($study) as $sub) {
                        if ($sub->getYear() == $year) {
                            $subscriptions[] = $sub;
                        }
                    }*/
                }

                $count = count($subscriptions);
                $this->debugTimer(
                    "Subscriptions collected for system {$system->getName()}:
                     {$count}. "
                );

                //echo '<pre>'; var_dump($subscriptions); die;
            } else {
                $subscriptions = $this->getSubscriptionModel()
                    ->findByStudyAndYear($this->getStudy()->getId(), $year);
            }

            $this->subscriptions[$key] = $subscriptions;
        }

        return $this->subscriptions[$key];
    }

    public function setObservation(Observation $observation)
    {
        $observation->getYear();
        $this->observation = $observation;

        return $this;
    }

    public function getObservation()
    {
        return $this->observation;
    }

    public function getYear()
    {
        return $this->getObservation()->getYear();
    }

    protected function getErrorLog($shortFormat = false)
    {
        $formatter = new Simple('%message%' . PHP_EOL);

        $writer = new Stream('error.log');

        if ($shortFormat) {
            $writer->setFormatter($formatter);
        }

        $logger = new Logger;
        $logger->addWriter($writer);

        return $logger;
    }

    protected function getChartFooter(Benchmark $benchmark)
    {
        $dataDefinition = $this->getVariableSubstitution()
            ->substitute($benchmark->getReportDescription(1));

        $dataDefinition .= ' [' . $this->getYear() . ' ' . $this->getStudy()->getName() . ']';

        return $dataDefinition;
    }

    protected function getBenchmark($dbColumn)
    {
        return $this->getBenchmarkModel()->findOneByDbColumnAndStudy($dbColumn, $this->getStudy()->getId());
    }
}
