<?php

namespace Mrss\Service\Report;

use MischiefCollective\ColorJizz\Formats\Hex;
use Mrss\Entity\Benchmark;
use Mrss\Service\Report;
use Mrss\Entity\PeerGroup;
use Mrss\Entity\College;
use Mrss\Entity\PeerBenchmark;
use Mrss\Entity\Benchmark as BenchmarkEntity;
use Mrss\Model\PeerBenchmark as PeerBenchmarkModel;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_Shared_Font;
use Mrss\Service\Report\Chart\Bar;

class Peer extends Report
{
    protected $peerBenchmarkModel;

    protected $showPeerDataYouDidNotSubmit = false;

    /**
     * @var \Mrss\Entity\College
     */
    protected $currentCollege;

    protected $year;

    protected $includePercentiles = true;

    public function getPeerReport($benchmarks, $colleges, $currentCollege, $year, $peerGroupName)
    {
        $minPeers = $this->getStudyConfig()->min_peers;

        $this->currentCollege = $currentCollege;

        $report = array(
            'skipped' => array(),
            'youHaveNoData' => array(),
            'sections' => array(),
            'colleges' => array(),
            'currentCollege' => $currentCollege->getNameAndState(),
            'year' => $year
        );

        //$benchmarks = $peerGroup->getBenchmarks();
        //$colleges = $peerGroup->getPeers();

        $colleges[] = $currentCollege->getId();

        $observations = array();
        $collegeEntities = array();

        // Fetch the colleges and their observation data for the year
        foreach ($colleges as $collegeId) {
            $college = $this->getCollegeModel()
                ->find($collegeId);

            $collegeEntities[$collegeId] = $college;
            $observations[$collegeId] = $college->getObservationForYear($year);

            if ($college->getId() != $currentCollege->getId()) {
                $report['colleges'][] = $college->getNameAndState();
            } elseif (!empty($observations[$collegeId])) {
                $this->setObservation($observations[$collegeId]);
            }
        }


        foreach ($benchmarks as $benchmarkId) {
            /** @var \Mrss\Entity\Benchmark $benchmark */
            $benchmark = $this->getBenchmarkModel()->find($benchmarkId);
            $this->logPeerBenchmark($benchmark, $currentCollege);

            // Build the report data
            $data = array();
            foreach ($collegeEntities as $college) {
                /** @var \Mrss\Entity\College $college */
                /** @var \Mrss\Entity\Observation $observation */

                $value = null;
                if (!empty($observations[$college->getId()])) {
                    $observation = $observations[$college->getId()];
                    $value = $observation->get($benchmark->getDbColumn());
                } else {
                    $this->getErrorLog()->err(
                        "Cannot find observation for college " . $college->getId() . " : " . $college->getName()
                    );
                }



                if ($value !== null) {
                    $data[$college->getId()] = $value;
                }
            }

            // Skip benchmarks with not enough peers
            if (count($data) <= $minPeers) {
                $report['skipped'][] = $benchmark->getPeerReportLabel();
                continue;
            }

            // Also skip benchmarks where the current college didn't report
            if (!isset($data[$currentCollege->getId()]) && !$this->getShowPeerDataYouDidNotSubmit()) {
                $report['youHaveNoData'][] = $benchmark->getPeerReportLabel();
                continue;
            }

            $data = $this->sortAndLabelPeerData($data, $currentCollege, $benchmark);

            if ($this->getIncludePercentiles()) {
                $data = $this->addPercentileRanks($data, $benchmark, $year);
            }

            // Data labels
            $prefix = $suffix = '';
            if ($benchmark->isPercent()) {
                $suffix = '%';
            } elseif ($benchmark->isDollars()) {
                $prefix = '$';
            }

            $series = array(
                array(
                    'name' => $benchmark->getPeerReportLabel(),
                    'data' => $data
                )
            );

            $reportSection = array(
                'benchmark' => $benchmark->getPeerReportLabel(),
                'decimal_places' => $this->getDecimalPlaces($benchmark),
                'data' => $data,// $series,//$data,
                'chart' => $this->getPeerBarChart(array($benchmark), $series),
                'prefix' => $prefix,
                'suffix' => $suffix,
                'isNumber' => $benchmark->isNumber()
            );

            $report['sections'][] = $reportSection;
        }

        $this->getPeerBenchmarkModel()->getEntityManager()->flush();

        return $report;
    }

    protected function addPercentileRanks($data, $benchmark, $year)
    {
        $newData = array();
        foreach ($data as $collegeId => $info) {
            $percentileRank = $this->getPercentileRankModel()->findOneByCollegeBenchmarkAndYear(
                $collegeId,
                $benchmark,
                $year
            );

            $rank = null;
            if ($percentileRank) {
                $rank = $percentileRank->getRank();
            }

            $info['percentileRank'] = $rank;

            $newData[$collegeId] = $info;
        }

        return $newData;
    }

    public function downloadPeerReport($report, $studyConfig)
    {
        $filename = 'peer-comparison-report';

        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();


        // Format for header row
        $blueBar = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'DCE6F1')
            )
        );

        // Peer comparison results
        $sheetIndex = 1;
        foreach ($report['sections'] as $section) {
            $row = 1;
            $sheet = $excel->createSheet($sheetIndex);

            $sheetName = $section['benchmark'];
            if (strlen($sheetName) > 20) {
                $sheetName = substr($sheetName, 0, 20);
            }

            // Get rid of invalid characters
            $sheetName = str_replace(array('*', ':', '/', '\\', '?', '[', ']'), '', $sheetName);

            try {
                $sheet->setTitle($sheetName);
            } catch (\Exception $e) {
                //pr($sheetName);
            }


            $headerRow = array(
                $section['benchmark'],
                ucwords($this->getStudyConfig()->benchmark_label)
            );

            if ($this->getStudyConfig()->peer_percentiles) {
                $headerRow[] = 'National % Rank';
            }

            $sheet->fromArray($headerRow, null, 'A' . $row);
            $sheet->getStyle("A$row:C$row")->applyFromArray($blueBar);
            $row++;

            foreach ($section['data'] as $collegeId => $peerData) {
                $institution = $peerData['label'];
                $value = $peerData['formatted'];

                $dataRow = array(
                    $institution,
                    $value,
                );

                if (!empty($peerData['percentileRank'])) {
                    $dataRow[] = round($peerData['percentileRank']);
                }



                $sheet->fromArray($dataRow, null, 'A' . $row);
                $row++;
            }


            // Align right
            $sheet->getStyle('B1:B400')->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            foreach (range(0, 2) as $column) {
                $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
            }

            if ($this->getStudyConfig()->anonymous_peers) {
                // Peer institutions
                $row++;
                $sheet->setCellValue('A' . $row, 'Peer ' . $studyConfig->institutions_label . ':');
                $row++;

                foreach ($report['colleges'] as $college) {
                    $sheet->setCellValue('A' . $row, $college);
                    $row++;
                }
            }

            $sheetIndex++;
        }

        // Remove blank sheet
        $excel->removeSheetByIndex(0);

        // redirect output to client browser
        $this->downloadExcel($excel, $filename);
    }

    /**
     * @return \Zend\Config\Config
     */
    public function getStudyConfig()
    {
        return $this->getServiceManager()->get('study');
    }

    private function shortenCollegeName($name)
    {
        $maxLength = 25;
        $suffix = "...";

        if (strlen($name) > $maxLength) {
            $name = substr($name, 0, $maxLength) . $suffix;
        }

        return $name;
    }

    public function sortAndLabelPeerData($data, College $currentCollege, Benchmark $benchmark, $sort = true)
    {
        $anonymous = $this->getStudyConfig()->anonymous_peers;

        if ($sort) {
            $data = $this->sortWithKeys($data);

            if ($benchmark->getIncludeInBestPerformer() && !$benchmark->getHighIsBetter()) {
                $data = array_reverse($data, TRUE);
            }
        }

        $dataWithLabels = array();

        $i = 1;
        foreach ($data as $collegeId => $value) {
            $label = null;
            if (!$anonymous) {
                if ($college = $this->getCollegeModel()->find($collegeId)) {
                    $label = $college->getNameAndState();
                }

            } elseif ($collegeId == $currentCollege->getId()) {
                $label = $currentCollege->getNameAndState();
            } else {
                $label = $this->numberToLetter($i);
                $i++;
            }

            //$dataWithLabels[$label] = $value;
            $dataWithLabels[$collegeId] = array(
                'label' => $label,
                'value' => $value,
                'formatted' => $benchmark->format($value)
            );
        }

        return $dataWithLabels;
    }

    /**
     * In the unlikely event that two colleges have the same value, this sort method orders those equal colleges
     * alphabetically by college name. So far, this should only apply to bond ratings.
     * @param $data
     * @return array
     */
    protected function sortWithKeys($data)
    {
        // First, convert the keys (college id) to part of the data
        $newData = array();
        foreach ($data as $key => $value) {
            $newData[] = array(
                'collegeId' => $key,
                'value' => $value
            );
        }

        // Now sort using an anonymous function
        usort($newData, function($a, $b)
        {
            $aValue = floatval($a['value']);
            $bValue = floatval($b['value']);

            if ($aValue == $bValue) {
                $aName = $this->getCollegeModel()->find($a['collegeId'])->getName();
                $bName = $this->getCollegeModel()->find($b['collegeId'])->getName();

                return ($aName > $bName);
            } else {
                return ($aValue < $bValue);
            }

        });

        // Now convert back to $collegeId => $value structure
        $newerData = array();
        foreach ($newData as $item) {
            $newerData[$item['collegeId']] = $item['value'];
        }

        return $newerData;
    }



    public function getYourCollegeColor()
    {
        //return '#002C57';
        //return '#9cc03e';
        return $this->getChartColor(0);
    }

    public function getPeerColor()
    {
        //return '#0065A1';
        return $this->getChartColor(1);
    }

    /**
     * @param BenchmarkEntity[] $benchmark
     * @param $data
     * @param null $title
     * @param null $subtitle
     * @param string $widthSetting
     * @param boolean $percentScaleZoom
     * @return array
     */
    public function getPeerBarChart(
        $benchmarks,
        $series,
        $title = null,
        $subtitle = null,
        $widthSetting = 'half',
        $percentScaleZoom = false,
        $highlightMyData = true
    ) {
        $benchmark = $benchmarks[0];

        $anonymous = $this->getStudyConfig()->anonymous_peers;

        if (empty($title)) {
            $title = $benchmark->getPeerReportLabel();
        }

        $decimalPlaces = $this->getDecimalPlaces($benchmark);

        $format = $this->getFormat($benchmark);

        $chartXCategories = array();



        $i = 0;
        foreach ($series as $key => $serie) {
            $isYours = false;

            $chartData = array();
            $data = $serie['data'];

            foreach ($data as $collegeId => $peerData) {
                $name = $peerData['label'];
                $value = round($peerData['value'], $decimalPlaces);

                $label = $this->shortenCollegeName($name);

                // Your college
                if ($highlightMyData && $name == $this->currentCollege->getNameAndState()) {
                    $isYours = true;
                    $dataLabelEnabled = true;
                    $color = $this->getYourCollegeColor();

                    if ($anonymous) {
                        //$label = 'Your College';
                        $label = $this->currentCollege->getAbbreviation();
                        if (empty($label)) {
                            $label = $this->currentCollege->getName();
                        }
                    }
                } else {
                    $dataLabelEnabled = false;
                    $color = $this->getPeerColor();
                }

                if ($i == 0) {
                    $chartXCategories[] = $label;
                }

                if (count($benchmarks) > 1) {
                    $color = $this->getColor($i, $isYours);
                }

                //$color = $this->adjustBrightness($color, ($i + 1) * 5);


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

            $series[$key]['data'] = $chartData;

            $i++;
        }



        /*$series = array(
            array(
                'name' => 'Value',
                'data' => $chartData
            )
        );*/


        // Set series colors
        foreach ($series as $key => $serie) {
            if ($color = $serie['data'][0]['color']) {
                $series[$key]['color'] = $color;
            }
        }

        $barChart = new Bar;
        $barChart->setTitle($title)
            ->setSubtitle($subtitle)
            ->setSeries($series)
            ->setXFormat($format)
            ->removeTickMarks()
            ->setWidth($widthSetting)
            ->setCategories($chartXCategories);

        if (count($benchmarks) == 1) {
            $barChart->setOrientationHorizontal();
            $barChart->setStacked(false);
            $barChart->enableLegend(false);
        } else {
            $barChart->setStacked(true);
            $barChart->enableLegend(true);
        }



        $forceScale = $this->getStudyConfig()->percent_chart_scale_1_100;
        if ($benchmark->isPercent() && $forceScale && !$percentScaleZoom) {
            $barChart->setYAxisMax(100);
            $barChart->setYAxisMin($this->getYMin($series));
        }


        return $barChart->getConfig();

        /*
        $seriesWithDataLabels = $this->forceDataLabelsInSeries($series);
        $dataDefinition = $this->getChartFooter($benchmark);

        $chart = array(
            'id' => 'chart_' . $benchmark->getDbColumn(),
            'chart' => array(
                'type' => 'bar',
                'events' => array(
                    'load' => 'loadChart'
                ),

            ),
            'exporting' => array(
                'chartOptions' => array(
                    'series' => $seriesWithDataLabels,
                    'chart' => array(
                        'spacingBottom' => ceil(strlen($dataDefinition) / 106) * 35 + 30,
                    )
                ),
            ),

            'title' => array(
                'text' => $title,
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
            'legend' => array(
                'enabled' => false
            ),
            'plotOptions' => array(
                'column' => array(
                    'animation' => false,
                    'dataLabels' => array(
                        'enabled' => true,
                        'format' => $format
                    )
                ),
                'series' => array(
                    'animation' => false
                )
            ),
            'dataDefinition' => $dataDefinition,
            'peerComparison' => true

        );

        // Percent
        if ($benchmark->isPercent()) {
            $chart['yAxis']['max'] = 100;
            $chart['yAxis']['tickInterval'] = 25;
            $chart['yAxis']['labels']['format'] = '{value}%';
        }

        return $chart;*/
    }

    function getColor($measureNumber, $yours = false)
    {
        $color = '#666'; // Default to grey

        $allColors = getSeriesColors();

        if ($yours) {
            $colors = $allColors['yourCollegeColors'];
        } else {
            $colors = $allColors['seriesColors'];
        }

        if (!empty($colors[$measureNumber])) {
            $color = $colors[$measureNumber];
        }

        return $color;
    }

    /**
     * Returns colleges that reported at least one of the benchmarks
     *
     * This method expects the college name to be unique, so it includes the state as well.
     *
     * @param College[] $colleges
     * @param array $benchmarkIds
     * @param $year
     * @return College[]
     */
    public function filterCollegesByBenchmarks($colleges, $benchmarkIds, $year)
    {
        $onlyIncludePeersReportingAllBenchmarks = false;

        $benchmarkCols = array();
        $benchmarkGroupIds = array();
        foreach ($benchmarkIds as $benchmarkId) {
            $benchmark = $this->getBenchmarkModel()->find($benchmarkId);

            if (empty($benchmark)) {
                continue;
            }

            $benchmarkCols[] = $benchmark->getDbColumn();
            $benchmarkGroupIds[] = $benchmark->getBenchmarkGroup()->getId();
        }

        $collegeIds = array();
        foreach ($colleges as $college) {
            $collegeIds[] = $college->getId();
        }

        $subscriptions = $this->getSubscriptionModel()
            ->findWithPartialObservations(
                $this->getStudy(),
                $year,
                $benchmarkCols,
                false,
                $onlyIncludePeersReportingAllBenchmarks,
                $benchmarkGroupIds
            );

        $filteredColleges = array();
        foreach ($subscriptions as $subscription) {
            $observation = $subscription->getObservation();

            //pr($subscription->getCollege()->getName());

            foreach ($benchmarkCols as $benchmarkCol) {
                $value = $observation->get($benchmarkCol);
                //pr("$benchmarkCol: $value");

                if ($value !== null) {
                    $college = $subscription->getCollege();

                    if (in_array($college->getId(), $collegeIds)) {
                        $filteredColleges[$college->getNameAndState()] = $college;
                        continue 2;
                    }
                }
            }
        }

        ksort($filteredColleges);

        return $filteredColleges;
    }

    public function logPeerBenchmark(BenchmarkEntity $benchmark, College $college)
    {
        $peerBenchmark = new PeerBenchmark();
        $peerBenchmark->setBenchmark($benchmark);
        $peerBenchmark->setCollege($college);
        $peerBenchmark->setStudy($benchmark->getBenchmarkGroup()->getStudy());

        // Save it
        $this->getPeerBenchmarkModel()->save($peerBenchmark);
    }

    public function setPeerBenchmarkModel(PeerBenchmarkModel $peerBenchmarkModel)
    {
        $this->peerBenchmarkModel = $peerBenchmarkModel;
        return $this;
    }

    /**
     * @return \Mrss\Model\PeerBenchmark
     */
    public function getPeerBenchmarkModel()
    {
        return $this->peerBenchmarkModel;
    }

    public function setShowPeerDataYouDidNotSubmit($show)
    {
        $this->showPeerDataYouDidNotSubmit = $show;

        return $this;
    }

    public function getShowPeerDataYouDidNotSubmit()
    {
        return $this->showPeerDataYouDidNotSubmit;
    }

    public function setCurrentCollege($college)
    {
        $this->currentCollege = $college;

        return $this;
    }

    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    public function getYear()
    {
        if (empty($this->year)) {
            $this->year = parent::getYear();
        }

        return $this->year;
    }

    public function setIncludePercentiles($setting)
    {
        $this->includePercentiles = $setting;

        return $this;
    }

    public function getIncludePercentiles()
    {
        return $this->includePercentiles;
    }

    function adjustBrightness($hex, $steps)
    {
        $color = Hex::fromString($hex);

        $adjusted = '#' . $color->brightness($steps * -0.8)->hue($steps * 2)->toHex()->__toString();

        return $adjusted;
    }

    protected function getYMin($series)
    {
        $yMin = 0;
        foreach ($series as $serie) {
            foreach ($serie['data'] as $datum) {
                if ($datum < $yMin) {
                    $yMin = $datum;
                }
            }
        }

        return $yMin;
    }
}
