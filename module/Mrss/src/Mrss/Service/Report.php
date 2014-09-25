<?php

namespace Mrss\Service;

use Mrss\Entity\Study;
use Mrss\Entity\Benchmark;
use Mrss\Entity\College;
use Mrss\Entity\Percentile;
use Mrss\Entity\PercentileRank;
use Mrss\Entity\Observation;
use Mrss\Entity\PeerGroup;
use Mrss\Entity\Outlier;
use Mrss\Service\Report\Calculator;
use Mrss\Service\ComputedFields;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\View\Renderer\RendererInterface;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_Shared_Font;

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
     * @var Smtp
     */
    protected $mailTransport;

    protected $debug = false;

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

            $key = $this->getOutliersCalculatedSettingKey($year);
            $yearsWithCalculationDates[$year]['outliers'] = $this->getSettingModel()
                ->getValueForIdentifier($key);
        }

        return $yearsWithCalculationDates;
    }

    public function calculateForYear($year)
    {
        $baseMemory = memory_get_usage();

        $start = microtime(1);
        $this->debug($year);
        // Update any computed fields
        $this->calculateAllComputedFields($year);
        $this->debugTimer($start, 'Just computed fields');

        $study = $this->getStudy();

        $calculator = $this->getCalculator();
        $breakpoints = $this->getPercentileBreakpoints();
        $percentileModel = $this->getPercentileModel();
        $percentileRankModel = $this->getPercentileRankModel();

        $percentileRankModel->getEntityManager()->flush();

        $this->debugTimer($start, 'About to clear values');
        // Clear the stored values
        $percentileModel->deleteByStudyAndYear($study->getId(), $year);
        $percentileRankModel->deleteByStudyAndYear($study->getId(), $year);
        $this->debugTimer($start, 'cleared values');
        // Take note of some stats
        $stats = array(
            'benchmarks' => 0,
            'percentiles' => 0,
            'percentileRanks' => 0,
            'noData' => 0
        );

        // Loop over benchmarks
        $benchmarks = $study->getBenchmarksForYear($year);
        $this->debug(count($benchmarks));
        $this->debugTimer($start, 'prep done.');

        foreach ($benchmarks as $benchmark) {
            /** @var Benchmark $benchmark */

            // Get all data points for this benchmark
            // Can't just pull from observations. have to consider subscriptions, too
            $data = $this->collectDataForBenchmark($benchmark, $year);

            if (empty($data)) {
                $stats['noData']++;
                continue;
            }

            $calculator->setData($data);

            // Percentiles
            foreach ($breakpoints as $breakpoint) {
                $value = $calculator->getValueForPercentile($breakpoint);

                $percentileEntity = new Percentile;
                $percentileEntity->setStudy($study);
                $percentileEntity->setYear($year);
                $percentileEntity->setBenchmark($benchmark);
                $percentileEntity->setPercentile($breakpoint);
                $percentileEntity->setValue($value);

                $percentileModel->save($percentileEntity);
                $stats['percentiles']++;
            }

            // Save the N (count) as a percentile
            $n = count($data);
            $percentileEntity = new Percentile;
            $percentileEntity->setStudy($study);
            $percentileEntity->setYear($year);
            $percentileEntity->setBenchmark($benchmark);
            $percentileEntity->setPercentile('N');
            $percentileEntity->setValue($n);

            $percentileModel->save($percentileEntity);

            // Percentile ranks
            foreach ($data as $collegeId => $datum) {
                $percentile = $calculator->getPercentileForValue($datum);

                if (false && $collegeId == 101 && $benchmark->getId() == 1) {
                    var_dump($data);
                    var_dump($datum);
                    var_dump($percentile);
                    die;
                }

                $percentileRank = new PercentileRank;
                $percentileRank->setStudy($study);
                $percentileRank->setYear($year);
                $percentileRank->setBenchmark($benchmark);
                $percentileRank->setRank($percentile);

                $college = $percentileRankModel->getEntityManager()
                    ->getReference('Mrss\Entity\College', $collegeId);
                $percentileRank->setCollege($college);

                $percentileRankModel->save($percentileRank);
                $stats['percentileRanks']++;
            }

            $stats['benchmarks']++;

            // Flush periodically
            if ($stats['benchmarks'] % 50 == 0) {
                $i = $stats['benchmarks'];
                //pr($stats['benchmarks']);
                //echo sprintf( '%8d: ', $i ), memory_get_usage() - $baseMemory, "\n<br>";
                $percentileModel->getEntityManager()->flush();
                //echo sprintf( '%8d: ', $i ), memory_get_usage() - $baseMemory, "\n<br>";
            }
        }

        // Update the settings table with the calculation date
        $settingKey = $this->getReportCalculatedSettingKey($year);
        $this->getSettingModel()->setValueForIdentifier($settingKey, date('c'));

        // Flush
        $percentileModel->getEntityManager()->flush();

        // Return some stats
        return $stats;
    }

    public function calculateOutliersForYear($year)
    {
        $this->calculateAllComputedFields($year);

        $stats = array(
            'high' => 0,
            'low' => 0,
            'missing' => 0
        );

        $start = microtime(1);

        $calculator = $this->getCalculator();

        // Clear any existing outliers for the year/study
        $studyId = $this->getStudy()->getId();
        $this->getOutlierModel()
            ->deleteByStudyAndYear($studyId, $year);
        $this->getOutlierModel()->getEntityManager()->flush();

        // Loop over the benchmarks
        foreach ($this->getStudy()->getBenchmarksForYear($year) as $benchmark) {
            /** @var Benchmark $benchmark */

            // Skip over computed benchmarks
            /*if ($benchmark->getComputed()) {
                continue;
            }*/

            // Get the data for all subscribers (skip nulls)
            $data = $this->collectDataForBenchmark($benchmark, $year);

            // If there's no data, move on
            if (empty($data)) {
                continue;
            }

            $calculator->setData($data);

            // Here's the key bit, where the outliers are actually calculated
            $outliers = $calculator->getOutliers();

            // Now save them
            foreach ($outliers as $outlierInfo) {
                $outlier = new Outlier;
                $outlier->setValue($outlierInfo['value']);
                $outlier->setBenchmark($benchmark);
                $outlier->setStudy($this->getStudy());
                $outlier->setYear($year);
                $problem = $outlierInfo['problem'];
                $outlier->setProblem($problem);
                $college = $this->getOutlierModel()->getEntityManager()
                    ->getReference('Mrss\Entity\College', $outlierInfo['college']);
                $outlier->setCollege($college);

                $this->getOutlierModel()->save($outlier);

                // Some stats
                $stats[$problem]++;
            }

            // Handle missing outliers
            if ($benchmark->getRequired()) {
                $data = $this->collectDataForBenchmark($benchmark, $year, false);

                foreach ($data as $collegeId => $datum) {
                    if ($datum === null) {
                        $outlier = new Outlier;
                        $outlier->setBenchmark($benchmark);
                        $outlier->setStudy($this->getStudy());
                        $outlier->setYear($year);
                        $problem = 'missing';
                        $outlier->setProblem($problem);
                        $college = $this->getOutlierModel()->getEntityManager()
                            ->getReference('Mrss\Entity\College', $collegeId);
                        $outlier->setCollege($college);

                        $this->getOutlierModel()->save($outlier);

                        // Some stats
                        $stats[$problem]++;
                    }
                }
            }
        }

        // Save the new report calculation date
        $settingKey = $this->getOutliersCalculatedSettingKey($year);
        $this->getSettingModel()->setValueForIdentifier($settingKey, date('c'));
        $this->getSettingModel()->getEntityManager()->flush();

        // Timer
        $end = microtime(1);
        $stats['time'] = round($end - $start, 1) . ' seconds';

        return $stats;
    }

    /**
     * Get the outliers for the study/year, grouped by college
     */
    public function getAdminOutlierReport($includeComputed = true)
    {
        $report = array();
        $study = $this->getStudy();
        $year = $study->getCurrentYear();

        // Get colleges subscribed to the study for the year
        $colleges = $this->getCollegeModel()->findByStudyAndYear(
            $study,
            $year
        );

        foreach ($colleges as $college) {
            // @todo: remove this hard-code for leaving Wake Tech and Grayson out
            if (in_array($college->getId(), array(296, 441))) {
                continue;
            }

            $outliers = $this->getOutlierModel()
                ->findByCollegeStudyAndYear($college, $study, $year);

            if (!$includeComputed) {
                $outliers = $this->removeComputedOutliers($outliers);
            }

            $report[] = array(
                'college' => $college,
                'outliers' => $outliers
            );
        }

        return $report;
    }

    /**
     * @param Outlier[] $outliers
     * @return Outlier[]
     */
    public function removeComputedOutliers($outliers)
    {
        $newList = array();

        foreach ($outliers as $outlier) {
            if (!$outlier->getBenchmark()->getComputed()) {
                $newList[] = $outlier;
            }
        }

        return $newList;
    }

    public function getOutlierReport(College $college)
    {
        $report = array();
        $study = $this->getStudy();
        $year = $study->getCurrentYear();

        $outliers = $this->getOutlierModel()
            ->findByCollegeStudyAndYear($college, $study, $year);
        $report[] = array(
            'college' => $college,
            'outliers' => $outliers
        );

        return $report;
    }

    public function emailOutliers(RendererInterface $renderer, $reallySend = true)
    {
        $reports = $this->getAdminOutlierReport(false);
        $stats = array('emails' => 0, 'preview' => '');

        // Loop over the admin report in order to send an email to each college
        foreach ($reports as $report) {
            /** @var \Mrss\Entity\College $college */
            $college = $report['college'];

            /** @var \Mrss\Entity\Outlier[] $outliers */
            $outliers = $report['outliers'];

            $studyName = $this->getStudy()->getDescription();
            $collegeName = $college->getName();
            $year = $this->getStudy()->getCurrentYear();

            $url = "www.workforceproject.org";
            $deadline = "July 18, " . date('Y');
            $replyTo = "michelletaylor@jccc.edu";
            $replyToName = "Michelle Taylor";
            $replyToPhone = "(913) 469-3831";

            $viewParams = array(
                'year' => $year,
                'studyName' => $studyName,
                'url' => $url,
                'deadline' => $deadline,
                'replyTo' => $replyTo,
                'replyToName' => $replyToName,
                'replyToPhone' => $replyToPhone,
                'outliers' => $outliers
            );

            // Select a view for the email body
            if (!empty($outliers)) {
                $view = 'mrss/report/outliers.email.phtml';

            } else {
                $view = 'mrss/report/outliers.email.none.phtml';
            }

            // Build the email body with the view
            $body = $renderer->render($view, $viewParams);

            // Email subject
            $subject = "Outlier report for $studyName";

            // Mime object for html body:
            $html = new MimePart($body);
            $html->type = 'text/html';

            $bodyPart = new MimeMessage;
            $bodyPart->setParts(array($html));

            $message = new Message;

            $message->setSubject($subject);
            $message->setBody($bodyPart);
            $message->addBcc('dfergu15@jccc.edu');
            $message->addFrom($replyTo, $replyToName);
            $message->setReplyTo($replyTo);


            if ($reallySend) {
                $message->addBcc($replyTo);

                // Get recipients
                foreach ($college->getUsers() as $user) {
                    // @todo: make this more dynamic
                    if ($college->getIpeds() == '155210'
                        && $user->getEmail() != 'jhoyer@jccc.edu') {
                        continue;
                    }


                    $message->addTo($user->getEmail(), $user->getFullName());
                }

                // Send it:
                $this->getMailTransport()->send($message);
            } else {
                $to = array();
                foreach ($college->getUsers() as $user) {
                    // @todo: make this more dynamic
                    if ($college->getIpeds() == '155210'
                        && $user->getEmail() != 'jhoyer@jccc.edu') {
                        continue;
                    }

                    $to[] = $user->getEmail() . '<' . $user->getFullName() . '>';
                }
                $to = implode(', ', $to);

                $stats['preview'] .= "Institution: $collegeName<br>\nTo: $to<br>\n<br>\n$body\n<hr><br>";
            }

            $stats['emails']++;
        }

        return $stats;
    }

    /**
     * Build a unique key for the year and study
     *
     * @param $year
     * @return string
     */
    public function getReportCalculatedSettingKey($year)
    {
        $studyId = $this->getStudy()->getId();

        $key = "report_calculated_{$studyId}_$year";

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
        $skipNull = true
    ) {
        $subscriptions = $this->getSubscriptions($year);

        $data = array();
        /** @var $subscription /Mrss/Entity/Subscription */
        foreach ($subscriptions as $subscription) {
            /** @var /Mrss/Entity/Observation $observation */
            if ($observation = $subscription->getObservation()) {
                $dbColumn = $benchmark->getDbColumn();
                $value = $observation->get($dbColumn);
                $collegeId = $subscription->getCollege()->getId();

                // Leave out null values
                if ($skipNull && $value === null) {
                    continue;
                }

                $data[$collegeId] = $value;
            }
        }

        return $data;
    }

    /**
     * Get the basic national percentile report in the form of nested
     * arrays, suitable for building an html, csv, or excel report.
     *
     * @param Observation $observation
     * @return array
     */
    public function getNationalReportData(Observation $observation)
    {
        $year = $observation->getYear();
        $reportData = array();

        $study = $this->getStudy();

        $benchmarkGroups = $study->getBenchmarkGroups();
        foreach ($benchmarkGroups as $benchmarkGroup) {
            $groupData = array(
                'benchmarkGroup' => $benchmarkGroup->getName(),
                'benchmarks' => array()
            );
            $benchmarks = $benchmarkGroup->getChildren($year);

            foreach ($benchmarks as $benchmark) {
                if (get_class($benchmark) == 'Mrss\Entity\BenchmarkHeading') {
                    /** @var \Mrss\Entity\BenchmarkHeading $heading */
                    $heading = $benchmark;
                    $groupData['benchmarks'][] = array(
                        'heading' => true,
                        'name' => $heading->getName()
                    );
                    continue;
                }

                if ($this->isBenchmarkExcludeFromReport($benchmark)) {
                    continue;
                }

                $benchmarkData = array(
                    'benchmark' => $benchmark->getReportLabel(),
                );

                $percentiles = $this->getPercentileModel()
                    ->findByBenchmarkAndYear($benchmark, $year);

                $percentileData = array();
                foreach ($percentiles as $percentile) {
                    $percentileData[$percentile->getPercentile()] =
                        $percentile->getValue();
                }

                // Pad the array if it's empty
                if (empty($percentileData)) {
                    $percentileData = array(null, null, null, null, null);
                }

                if (!empty($percentileData['N'])) {
                    $benchmarkData['N'] = $percentileData['N'];
                    unset($percentileData['N']);
                } else {
                    $benchmarkData['N'] = '';
                }


                $benchmarkData['percentiles'] = $percentileData;

                $benchmarkData['reported'] = $observation->get(
                    $benchmark->getDbColumn()
                );

                $benchmarkData['reported_decimal_places'] = $this
                    ->getDecimalPlaces($benchmark->getDbColumn());

                $percentileRank = $this->getPercentileRankModel()
                    ->findOneByCollegeBenchmarkAndYear(
                        $observation->getCollege(),
                        $benchmark,
                        $year
                    );

                if (!empty($percentileRank)) {
                    $benchmarkData['percentile_rank_id'] = $percentileRank->getId();
                    $benchmarkData['percentile_rank'] = $percentileRank->getRank();

                    // Show - rather than 0 percentile
                    if ($benchmarkData['reported'] == 0) {
                        $benchmarkData['percentile_rank'] = '-';
                    }
                } else {
                    $benchmarkData['percentile_rank_id'] = '';
                    $benchmarkData['percentile_rank'] = '';
                }

                // Data labels
                $prefix = $suffix = '';
                if ($benchmark->isPercent()) {
                    $suffix = '%';
                } elseif ($benchmark->isDollars()) {
                    $prefix = '$';
                }

                $benchmarkData['prefix'] = $prefix;
                $benchmarkData['suffix'] = $suffix;

                // Chart
                $chartConfig = array('dbColumn' => $benchmark->getDbColumn());
                $benchmarkData['chart'] = $this->getPercentileBarChart(
                    $chartConfig,
                    $observation
                );

                $benchmarkData['description'] = $benchmark->getDescription();

                $groupData['benchmarks'][] = $benchmarkData;
            }

            $reportData[] = $groupData;
        }

        //echo '<pre>' . print_r($reportData, 1) . '</pre>';
        return $reportData;
    }

    public function downloadNationalReport($reportData)
    {
        $filename = 'national-report';

        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $row = 1;

        // Format for header row
        $blueBar = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'DCE6F1')
            )
        );

        foreach ($reportData as $benchmarkGroup) {
            // Header
            $headerRow = array(
                $benchmarkGroup['benchmarkGroup'],
                'Reported Value',
                '% Rank',
                'N'
            );

            foreach ($this->getPercentileBreakPointLabels() as $breakpoint) {
                $headerRow[] = strip_tags($breakpoint);
            }

            $sheet->fromArray($headerRow, null, 'A' . $row);
            $sheet->getStyle("A$row:I$row")->applyFromArray($blueBar);
            $row++;

            // Data
            foreach ($benchmarkGroup['benchmarks'] as $benchmark) {
                if (null !== $benchmark['reported']) {
                    $reported = $benchmark['prefix'] .
                        number_format(
                            $benchmark['reported'],
                            $benchmark['reported_decimal_places']
                        ) .
                        $benchmark['suffix'];
                } else {
                    $reported = null;
                };

                if ($benchmark['percentile_rank'] == '-') {
                    $rank = '-';
                } elseif ($benchmark['percentile_rank']) {
                    $rank = round($benchmark['percentile_rank']) . '%';
                } else {
                    $rank = null;
                }

                $dataRow = array(
                    $benchmark['benchmark'],
                    $reported,
                    $rank,
                    $benchmark['N']
                );

                foreach ($benchmark['percentiles'] as $percentile) {
                    $dataRow[] = $benchmark['prefix'] .
                        number_format(
                            $percentile,
                            $benchmark['reported_decimal_places']
                        ) . $benchmark['suffix'];
                }

                $sheet->fromArray($dataRow, null, 'A' . $row);
                $row++;
            }

            // Add a blank row after each form
            $row++;
        }

        // Align right
        $sheet->getStyle('B1:I400')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // Set column widths
        PHPExcel_Shared_Font::setAutoSizeMethod(
            PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT
        );
        foreach (range(0, 8) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        // redirect output to client browser
        $this->downloadExcel($excel, $filename);
    }

    /**
     * Most reported values should be rounded to 0 decimal places.
     * These are the exceptions
     *
     * @param $dbColumn
     * @return int
     */
    public function getDecimalPlaces($dbColumn)
    {
        $map = array(
            'enrollment_information_contact_hours_per_student' => 1,
            'enrollment_information_market_penetration' => 1
        );

        if (isset($map[$dbColumn])) {
            $decimalPlaces = $map[$dbColumn];
        } else {
            $decimalPlaces = 0;
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
        $benchmark = $this->getBenchmarkModel()->findOneByDbColumn($dbColumn);

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

    public function getPieChart($chartConfig, Observation $observation)
    {
        $colors = $this->getPieChartColors();
        $data = array();
        foreach ($chartConfig['benchmarks'] as $i => $benchmark) {
            // Nationl median or college's reported value?
            if (!empty($benchmark['median'])) {
                $benchmarkEntity = $this->getBenchmarkModel()->findOneByDbColumn(
                    $benchmark['dbColumn']
                );

                $value = $this->getPercentileModel()
                    ->findByBenchmarkYearAndPercentile(
                        $benchmarkEntity->getId(),
                        $observation->getYear(),
                        50
                    )->getValue();
            } else {
                $value = $observation->get($benchmark['dbColumn']);
            }

            // Skip zero values
            if (empty($value)) {
                continue;
            }

            $title = $benchmark['title'];

            $data[] = array(
                'name' => $title,
                'y' => $value,
                'color' => $colors[$i]
            );
        }

        $series = array(
            array(
                'name' => 'Value',
                'data' => $data
            )
        );


        $chart = array(
            'id' => 'chart_' . uniqid(),
            'chart' => array(
                'type' => 'pie'
            ),
            'title' => array(
                'text' => $chartConfig['title'],
            ),
            'series' => $series,
            'credits' => array(
                'enabled' => false
            ),
        );

        return $chart;
    }

    public function getBubbleChart($x, $y, $size, $title)
    {
        $study = $this->getStudy();

        $subscriptions = $this->getSubscriptionModel()
            ->findByStudyAndYear($study->getId(), $study->getCurrentYear());

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
            $chartConfig['title'] = $benchmark->getReportLabel();
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


        $format = "{y}";
        if ($benchmark->isPercent()) {
            $format = "{y}%";
        } elseif ($benchmark->isDollars()) {
            $format = '${y}';
        }

        // Put the college's data in its place
        $chartValues = array_combine($chartXCategories, $chartValues);
        asort($chartValues);
        $chartXCategories = array_keys($chartValues);
        $roundTo = $this->getDecimalPlaces($benchmark->getDbColumn());

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


        $chart = array(
            'id' => 'chart_' . $benchmark->getDbColumn(),
            'chart' => array(
                'type' => 'column'
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
                'gridLineWidth' => 0
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

        if ($benchmark->isPercent()) {
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
        }

        //var_dump($chartConfig);
        //var_dump($chart);
        return $chart;
    }

    public function getPeerBarChart(Benchmark $benchmark, $data)
    {
        $title = $benchmark->getName();
        //prd($data);

        $chartData = array();
        $chartXCategories = array();
        foreach ($data as $name => $value) {
            $value = round($value);

            $label = $name;

            $chartXCategories[] = $label;

            // Your college
            if (strlen($name) > 2) {
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
                    'overflow' => 'none'
                )
            );
        }

        $series = array(
            array(
                'name' => 'Value',
                'data' => $chartData
            )
        );


        $chart = array(
            'id' => 'chart_' . $benchmark->getDbColumn(),
            'chart' => array(
                'type' => 'column'
            ),
            'title' => array(
                //'text' => $title,
            ),
            'xAxis' => array(
                'categories' => $chartXCategories,
                'tickLength' => 0,
                'labels' => array(
                    'maxStaggerLines' => 1
                )

                //'title' => array(
                //    'text' => 'Percentiles'
                //)
            ),
            'yAxis' => array(
                'title' => false,
                'gridLineWidth' => 0
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

        return $chart;
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

    public function getBenchmarksToExcludeFromReport()
    {
        return array(
            'institutional_demographics_campus_environment',
            'institutional_demographics_staff_unionized',
            'institutional_demographics_faculty_unionized',
        );
    }

    public function isBenchmarkExcludeFromReport(Benchmark $benchmark)
    {
        $toExclude = $this->getBenchmarksToExcludeFromReport();

        $manualExclude = in_array($benchmark->getDbColumn(), $toExclude);

        $inputTypesToExclude = array('radio');
        $inputTypeExclude = in_array(
            $benchmark->getInputType(),
            $inputTypesToExclude
        );

        // Now look at the checkbox
        if (!$benchmark->getIncludeInNationalReport()) {
            $manualExclude = true;
        }

        return ($manualExclude || $inputTypeExclude);
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

    public function getPeerReport(PeerGroup $peerGroup)
    {
        $minPeers = 5;

        $report = array(
            'skipped' => array(),
            'sections' => array(),
            'colleges' => array(),
            'currentCollege' => $peerGroup->getCollege()->getName(),
            'year' => $peerGroup->getYear()
        );

        $year = $peerGroup->getYear();
        $benchmarks = $peerGroup->getBenchmarks();
        $colleges = $peerGroup->getPeers();
        $colleges[] = $peerGroup->getCollege()->getId();

        $observations = array();
        $collegeEntities = array();

        // Fetch the colleges and their observation data for the year
        foreach ($colleges as $collegeId) {
            $college = $this->getCollegeModel()
                ->find($collegeId);

            $collegeEntities[$collegeId] = $college;
            $observations[$collegeId] = $college->getObservationForYear($year);

            if ($college->getId() != $peerGroup->getCollege()->getId()) {
                $report['colleges'][] = $college->getName();
            }
        }


        foreach ($benchmarks as $benchmarkId) {
            $benchmark = $this->getBenchmarkModel()->find($benchmarkId);

            // Build the report data
            $data = array();
            foreach ($collegeEntities as $college) {
                /** @var \Mrss\Entity\College $college */
                /** @var \Mrss\Entity\Observation $observation */

                $observation = $observations[$college->getId()];
                $value = $observation->get($benchmark->getDbColumn());

                if ($value !== null) {
                    $data[$college->getId()] = $value;
                }
            }

            // Skip benchmarks with not enough peers
            if (count($data) <= $minPeers) {
                $report['skipped'][] = $benchmark->getPeer();
                continue;
            }

            // Also skip benchmarks where the current college didn't report
            if (!isset($data[$peerGroup->getCollege()->getId()])) {
                continue;
            }

            $data = $this->sortAndLabelPeerData($data, $peerGroup->getCollege());

            $reportSection = array(
                'benchmark' => $benchmark->getPeerReportLabel(),
                'data' => $data,
                'chart' => $this->getPeerBarChart($benchmark, $data)
            );

            $report['sections'][] = $reportSection;
        }


        return $report;
    }

    public function downloadPeerReport($report, $peerGroup)
    {
        $filename = 'peer-comparison-report';

        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $row = 1;

        // Format for header row
        $blueBar = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'DCE6F1')
            )
        );

        // Peer comparison results
        foreach ($report['sections'] as $section) {
            $headerRow = array(
                $section['benchmark'],
                null
            );

            $sheet->fromArray($headerRow, null, 'A' . $row);
            $sheet->getStyle("A$row:B$row")->applyFromArray($blueBar);
            $row++;

            foreach ($section['data'] as $institution => $value) {
                $dataRow = array(
                    $institution,
                    round($value)
                );

                $sheet->fromArray($dataRow, null, 'A' . $row);
                $row++;
            }

            // Blank line:
            $row++;
        }

        // Align right
        $sheet->getStyle('B1:B400')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // Set column widths
        PHPExcel_Shared_Font::setAutoSizeMethod(
            PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT
        );
        foreach (range(0, 1) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }


        // Peer institutions
        $row++;
        $sheet->setCellValue('A' . $row, 'Peer Institutions:');
        $row++;

        foreach ($report['colleges'] as $college) {
            $sheet->setCellValue('A' . $row, $college);
            $row++;
        }



        // redirect output to client browser
        $this->downloadExcel($excel, $filename);

    }

    public function sortAndLabelPeerData($data, College $currentCollege)
    {
        arsort($data);
        $dataWithLabels = array();

        $i = 1;
        foreach ($data as $collegeId => $value) {
            if ($collegeId == $currentCollege->getId()) {
                $label = $currentCollege->getName();
            } else {
                $label = $this->numberToLetter($i);
                $i++;
            }

            $dataWithLabels[$label] = $value;
        }

        return $dataWithLabels;
    }

    /**
     * Calculate all computed fields for the current study and the given year
     *
     * @param $year
     */
    public function calculateAllComputedFields($year)
    {
        $subs = $this->getSubscriptions($year);
        $start = microtime(1);

        foreach ($subs as $sub) {
            $observation = $sub->getObservation();
            if ($observation) {
                $this->getComputedFieldsService()
                    ->calculateAllForObservation($observation, $this->getStudy());
            } else {
                //echo "Observation missing for " . $sub->getCollege()->getName() .
                //    " " . $sub->getYear();
                //die;
            }
            $el = microtime(1) - $start;
            //pr(round($el, 3));
            unset($observation);
            //die('blkajsdls');
        }
        //die('calculated');
    }

    /**
     * Returns colleges that reported at least one of the benchmarks
     *
     * @param College[] $colleges
     * @param array $benchmarkIds
     * @param $year
     * @return College[]
     */
    public function filterCollegesByBenchmarks($colleges, $benchmarkIds, $year)
    {
        $benchmarkCols = array();
        foreach ($benchmarkIds as $benchmarkId) {
            $benchmark = $this->getBenchmarkModel()->find($benchmarkId);

            if (empty($benchmark)) {
                continue;
            }

            $benchmarkCols[] = $benchmark->getDbColumn();
        }

        $filteredColleges = array();
        foreach ($colleges as $college) {
            $observation = $college->getObservationForYear($year);

            foreach ($benchmarkCols as $benchmarkCol) {
                $value = $observation->get($benchmarkCol);
                if ($value !== null) {
                    $filteredColleges[] = $college;
                    continue 2;
                }
            }

        }
        return $filteredColleges;
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

    public function setCalculator(Calculator $calculator)
    {
        $this->calculator = $calculator;

        return $this;
    }

    public function getCalculator()
    {
        return $this->calculator;
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

    protected function debugTimer($start, $message = null)
    {
        if ($this->debug) {
            $elapsed = round(microtime(1) - $start, 3);
            $message = $elapsed . "s: " . $message;
            $this->debug($message);
        }
    }

    /**
     * @param $year
     * @return \Mrss\Entity\Subscription[]
     */
    protected function getSubscriptions($year)
    {
        if (empty($this->subscriptions[$year])) {
            $this->subscriptions[$year] = $this->getSubscriptionModel()
                ->findByStudyAndYear($this->getStudy()->getId(), $year);

        }

        return $this->subscriptions[$year];
    }
}
