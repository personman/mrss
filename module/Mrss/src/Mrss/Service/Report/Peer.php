<?php

namespace Mrss\Service\Report;

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

class Peer extends Report
{
    protected $peerBenchmarkModel;

    protected $showPeerDataYouDidNotSubmit = false;

    /**
     * @var \Mrss\Entity\College
     */
    protected $currentCollege;

    public function getPeerReport($benchmarks, $colleges, $currentCollege, $year, $peerGroupName)
    {
        $minPeers = 5;
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

                $observation = $observations[$college->getId()];
                $value = $observation->get($benchmark->getDbColumn());

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

            $data = $this->sortAndLabelPeerData($data, $currentCollege);

            // Data labels
            $prefix = $suffix = '';
            if ($benchmark->isPercent()) {
                $suffix = '%';
            } elseif ($benchmark->isDollars()) {
                $prefix = '$';
            }

            $reportSection = array(
                'benchmark' => $benchmark->getPeerReportLabel(),
                'decimal_places' => $this->getDecimalPlaces($benchmark),
                'data' => $data,
                'chart' => $this->getPeerBarChart($benchmark, $data),
                'prefix' => $prefix,
                'suffix' => $suffix
            );

            $report['sections'][] = $reportSection;
        }

        $this->getPeerBenchmarkModel()->getEntityManager()->flush();

        return $report;
    }

    public function downloadPeerReport($report)
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
            } catch ( \Exception $e) {
                //pr($sheetName);
            }


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


            // Align right
            $sheet->getStyle('B1:B400')->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            foreach (range(0, 1) as $column) {
                $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
            }

            if ($this->getStudyConfig()->anonymous_peers) {
                // Peer institutions
                $row++;
                $sheet->setCellValue('A' . $row, 'Peer Institutions:');
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

    public function sortAndLabelPeerData($data, College $currentCollege)
    {
        $anonymous = $this->getStudyConfig()->anonymous_peers;

        arsort($data);
        $dataWithLabels = array();

        $i = 1;
        foreach ($data as $collegeId => $value) {
            if (!$anonymous) {
                $college = $this->getCollegeModel()->find($collegeId);
                $label = $college->getNameAndState();
            } elseif ($collegeId == $currentCollege->getId()) {
                $label = $currentCollege->getNameAndState();
            } else {
                $label = $this->numberToLetter($i);
                $i++;
            }

            $dataWithLabels[$label] = $value;
        }

        return $dataWithLabels;
    }

    public function getYourCollegeColor()
    {
        //return '#002C57';
        return '#9cc03e';
    }

    public function getPeerColor()
    {
        return '#0065A1';
    }

    public function getPeerBarChart(BenchmarkEntity $benchmark, $data)
    {
        $anonymous = $this->getStudyConfig()->anonymous_peers;
        $title = $benchmark->getPeerReportLabel();
        $decimalPlaces = $this->getDecimalPlaces($benchmark);
        //prd($data);

        $format = $this->getFormat($benchmark);

        $chartData = array();
        $chartXCategories = array();
        foreach ($data as $name => $value) {
            $value = round($value, $decimalPlaces);

            $label = $this->shortenCollegeName($name);

            // Your college
            if ($name == $this->currentCollege->getNameAndState()) {
                $dataLabelEnabled = true;
                $color = $this->getYourCollegeColor();

                if ($anonymous) {
                    $label = 'Your College';
                }
            } else {
                $dataLabelEnabled = false;
                $color = $this->getPeerColor();
            }

            $chartXCategories[] = $label;

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

        $seriesWithDataLabels = $this->forceDataLabelsInSeries($series);
        $dataDefinition = $this->getChartFooter($benchmark, $year);

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

        return $chart;
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
}
