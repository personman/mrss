<?php

namespace Mrss\Service\Report\ChartBuilder;

use Mrss\Service\Report\ChartBuilder;
use Mrss\Service\Report\Chart\Line;
use Mrss\Service\Report\Calculator;

class LineBuilder extends ChartBuilder
{
    // The years the college subscribed. Don't show them data if they were not members
    protected $years = array();
    protected $peerData = array();

    public function setYears($years)
    {
        $this->years = $years;

        return $this;
    }

    public function getYears()
    {
        return $this->years;
    }

    public function hasYear($year)
    {
        return in_array($year, $this->getYears());
    }

    public function getChart()
    {
        $dbColumns = $this->getDbColumns();
        $peerGroup = $this->getPeerGroup();
        $title = $this->getTitle();
        $subtitle = $this->getSubtitle();
        $config = $this->getConfig();

        $allData = $this->getAllData();


        // @todo: make peer cohort needs to handle both benchmarks. only want peers who submitted both for each year
        // @todo: show only one peer footnote


        $series = $this->getSeries($allData, $peerGroup);

        // First benchmark:
        foreach ($dbColumns as $dbColumn) {
            $benchmark = $this->getBenchmark($dbColumn);
            $data = $allData[$dbColumn]['data'];

            // Peer footnote
            if ($peerIds = $allData[$dbColumn]['peerIds']) {
                $peerFootnote = $this->getPeerFootnote($peerIds);

                if (!empty($peerFootnote)) {
                    $this->addFootnote($peerGroup->getName() . ': ' . $peerFootnote);
                }

            }
            break;
        }



        $xCategories = $this->offsetYears(array_keys($data), $benchmark->getYearOffset());
        $yLabel = $benchmark->getDescriptiveReportLabel();
        if (!empty($config['multiTrend'])) {
            $yLabel = '';
        }

        $chart = new Line;
        $chart->setTitle($title)
            ->setSubtitle($subtitle)
            ->setYLabel($yLabel)
            ->setYFormat($this->getFormat($benchmark))
            ->setCategories($xCategories)
            ->setSeries($series);

        // Percentages should have the axis as 0-100
        $forceScale = $this->getStudyConfig()->percent_chart_scale_1_100;
        if ($benchmark->isPercent() && empty($config['percentScaleZoom']) && $forceScale) {
            $chart->setYAxisMax(100);
            $chart->setYAxisMin(0);
        }


        return $chart->getConfig();
    }

    public function getDbColumns()
    {
        $config = $this->getConfig();

        $dbColumns = array($config['benchmark2']);
        if (!empty($config['multiTrend'])) {
            $dbColumns[] = $config['benchmark3'];
        }

        return $dbColumns;
    }

    public function getPeerGroup()
    {
        $config = $this->getConfig();
        $peerGroup = null;
        $peerGroupConfig = $config['peerGroup'];

        if ($peerGroupConfig) {
            $peerGroupModel = $this->getPeerGroupModel();
            $peerGroup = $peerGroupModel->find($peerGroupConfig);
        }

        return $peerGroup;
    }

    public function getTitle()
    {
        $config = $this->getConfig();

        $title = $config['title'];

        if (empty($title)) {
            $title = array();
            foreach ($this->getDbColumns() as $dbColumn) {
                $benchmark = $this->getBenchmark($dbColumn);
                $title[] = $benchmark->getDescriptiveReportLabel();
            }

            $title = implode(' and ', $title);
        }

        return $title;
    }

    public function getSubtitle()
    {
        $config = $this->getConfig();
        $subtitle = null;
        if (!empty($config['subtitle'])) {
            $subtitle = $config['subtitle'];
        }

        return $subtitle;
    }

    public function getAllData()
    {
        $config = $this->getConfig();
        $dbColumns = $this->getDbColumns();

        $allData = array();

        $peerGroupConfig = $config['peerGroup'];
        $peerGroupModel = $this->getPeerGroupModel();
        $peerGroup = $peerGroupModel->find($peerGroupConfig);

        foreach ($dbColumns as $dbColumn) {
            $benchmark = $this->getBenchmark($dbColumn);

            if ($definition = $benchmark->getReportDescription(true)) {
                $this->addFootnote($benchmark->getDescriptiveReportLabel() . ': ' . $definition);
            }

            if (empty($title)) {
                $title = $benchmark->getDescriptiveReportLabel();
            }

            // Get the college's reported data
            $data = $this->getDataForCollege($dbColumn);

            $percentiles = $config['percentiles'];
            //$percentiles = array(50);

            $mediansData = array();
            $peerMediansData = array();
            foreach ($percentiles as $percentile) {
                $medianData = $this->getMedianData($benchmark, $percentile);

                list($peerMedianData, $peerIds) = $this->getPeerMedianData(
                    $peerGroup,
                    $benchmark,
                    $percentile
                );

                $mediansData[$percentile] = $medianData;
                $peerMediansData[$percentile] = $peerMedianData;
            }

            $allData[$dbColumn] = array(
                'data' => $data,
                'mediansData' => $mediansData,
                'peerMediansData' => $peerMediansData,
                'peerIds' => $peerIds
            );
        }

        return $allData;
    }

    public function getSeries($allData, $peerGroup = null)
    {
        $config = $this->getConfig();

        // Build the series
        $series = array();

        $i = 0;
        foreach ($allData as $dbColumn => $dataForBenchmark) {
            $data = $dataForBenchmark['data'];
            $mediansData = $dataForBenchmark['mediansData'];
            $peerMediansData = $dataForBenchmark['peerMediansData'];
            $peerIds = $dataForBenchmark['peerIds'];

            if (empty($config['hideMine'])) {
                $name = $this->getCollege()->getName();
                if (!empty($config['multiTrend'])) {
                    $benchmark = $this->getBenchmark($dbColumn);
                    $name .= '|' . $benchmark->getDescriptiveReportLabel();
                }

                $series[] = array(
                    'name' => $name,
                    'data' => array_values($data),
                    'color' => $this->getYourColor($i)
                );
            }

            if (empty($config['hideNational'])) {
                foreach ($mediansData as $percentile => $medianData) {
                    if ($percentile == 50) {
                        $label = 'Median';
                    } else {
                        $label = $this->getOrdinal($percentile);
                    }

                    $nationalOrNetwork = 'National';
                    if (isset($config['system'])) {
                        $system = $this->getSystemModel()->find($config['system']);
                        $nationalOrNetwork = $system->getName();
                    }

                    $nationalLabel = "$nationalOrNetwork $label";
                    if (!empty($config['multiTrend'])) {
                        $benchmark = $this->getBenchmark($dbColumn);
                        $nationalLabel .= '|' . $benchmark->getDescriptiveReportLabel();
                    }


                    $series[] = array(
                        'name' => $nationalLabel,
                        'data' => array_values($medianData),
                        'color' => $this->getNationalColor($i)
                    );
                }
            }


            if (!empty($peerGroup) && !empty($peerIds) && count($peerIds) >= $this->minimumPeers) {
                foreach ($peerMediansData as $percentile => $peerMedianData) {
                    if ($percentile == 50) {
                        $label = 'Median';
                    } else {
                        $label = $this->getOrdinal($percentile);
                    }

                    $series[] = array(
                        'name' => $peerGroup->getName() . ' ' . $label,
                        'data' => array_values($peerMedianData),
                        'color' => $this->getPeerColor($i)
                    );
                }
            }

            $i++;
        }


        return $series;
    }

    public function getDataForCollege($dbColumn)
    {
        // Get the college's reported data
        $subscriptions = $this->getCollege()->getSubscriptionsForStudy($this->getStudy());
        $data = array();
        foreach ($subscriptions as $subscription) {
            // Skip current year if reporting isn't open yet.
            if ($this->getStudy()->getCurrentYear() == $subscription->getYear()
                && !$this->getStudy()->getReportsOpen()) {
                continue;
            }

            $value = $subscription->getObservation()->get($dbColumn);
            if ($value !== null) {
                $value = floatval($value);
            }

            $data[$subscription->getYear()] = $value;
        }
        ksort($data);


        $this->setYears(array_keys($data));
        $data = $this->fillInGaps($data);

        return $data;
    }

    public function getMedianData($benchmark, $percentile)
    {
        $systemId = $this->getSystemId();
        $medians = $this->getPercentileModel()->findByBenchmarkAndPercentile($benchmark, $percentile, false, $systemId);

        $medianData = array();
        foreach ($medians as $median) {
            // Don't show any data for years they didn't subscribe
            if (!$this->hasYear($median->getYear())) {
                continue;
            }
            $medianData[$median->getYear()] = floatval($median->getValue());
        }
        ksort($medianData);

        return $this->fillInGaps($medianData);
    }

    public function getPeerMedianData($peerGroup, $benchmark, $percentile)
    {
        $dbColumn = $benchmark->getDbColumn();

        // Peer group median
        $peerMedianData = array();
        $peerIds = array();
        if (!empty($peerGroup)) {
            if ($peerGroup) {
                list($peerMedianData, $peerIds) = $this->getPeerMedians(
                    $peerGroup,
                    $dbColumn,
                    $percentile
                );
            }
        }

        $peerMedianData = $this->fillInGaps($peerMedianData);

        return array($peerMedianData, $peerIds);
    }

    public function getPeerFootnote($peerIds)
    {
        $peerFootnote = "(Select a peer group with at least {$this->minimumPeers} data points.)";
        if (count($peerIds) >= $this->minimumPeers) {
            $this->setPeers($peerIds);

            $includedPeers = $this->getCollegeModel()->findByIds($peerIds);
            $peerNames = array();
            foreach ($includedPeers as $peer) {
                $peerNames[] = $peer->getNAme();
            }

            $peerFootnote = implode(', ', $peerNames);
        }

        return $peerFootnote;
    }

    public function syncArrays($array1, $array2)
    {
        $keys = array_unique(array_merge(array_keys($array1), array_keys($array2)));
        ksort($keys);

        $new1 = array();
        $new2 = array();
        foreach ($keys as $key) {
            if (isset($array1[$key])) {
                $new1[$key] = $array1[$key];
            } else {
                $new1[$key] = null;
            }

            if (isset($array2[$key])) {
                $new2[$key] = $array2[$key];
            } else {
                $new2[$key] = null;
            }
        }

        return array($new1, $new2);
    }

    public function fillInGaps($array)
    {
        $years = $this->getYearRange();

        $newArray = array();
        foreach ($years as $year) {
            if (isset($array[$year])) {
                $newArray[$year] = $array[$year];
            } else {
                $newArray[$year] = null;
            }
        }

        return $newArray;
    }

    public function getYearRange()
    {
        $years = $this->getYears();
        $start = min($years);
        $end = max($years);

        $years = range($start, $end);

        return $years;
    }

    protected function getPeerMedians($peerGroup, $dbColumn, $breakpoint = 50)
    {
        $years = $this->getYears();

        list($allPeersData, $collegeIds) = $this->getAllPeerData($peerGroup, $years);
        $peersData = $allPeersData[$dbColumn];

        /*$peersData = array();
        foreach ($years as $year) {
            $peersData[$year] = $this->getPeerData($peerGroup, $dbColumn, $year);
        }
        list($peersData, $collegeIds) = $this->makePeerCohort($peersData);
        */

        $peerMedians = array();
        foreach ($years as $year) {
            if (empty($peersData[$year])) {
                $peerMedians[$year] = null;
            } else {
                $dataForYear = array();
                foreach ($peersData[$year] as $value) {
                    $dataForYear[] = floatval($value);
                }

                $calculator = new Calculator;
                $calculator->setData($dataForYear);

                $result = $calculator->getValueForPercentile($breakpoint);

                $peerMedians[$year] = $result;
            }
        }

        // If the number of peers is below the minimum, don't show any peer data
        if (count($collegeIds) < $this->minimumPeers) {
            $peerMedians = array();
            $collegeIds = array();
        }

        return array($peerMedians, $collegeIds);
    }

    public function getAllPeerData($peerGroup, $years)
    {
        $peersData = array();
        foreach ($this->getDbColumns() as $dbColumn) {
            $peersData[$dbColumn] = array();
            foreach ($years as $year) {
                $peersData[$dbColumn][$year] = $this->getPeerData($peerGroup, $dbColumn, $year);
            }
        }

        // Now make a cohort from all this data. To be included, peers have to submit all data points for all years
        list($peersData, $collegeIds) = $this->makeCohortForMultiTrend($peersData);

        return array($peersData, $collegeIds);
    }

    public function makeCohortForMultiTrend($peersData)
    {
        $config = $this->getConfig();
        $makePeerCohort = !empty($config['makePeerCohort']);

        $newData = array();
        $peerIds = array();
        foreach ($peersData as $dbColumn => $peerData) {
            //list($data, $peerIds) = $this->makePeerCohort($peerData);
            //pr($peerIds);
            //pr(count($peerIds));

            if ($makePeerCohort) {
                $otherData = $this->getOtherData($peersData, $dbColumn);

                list($data, $peerIds) = $this->makePeerCohort($peerData, $otherData);


            } else {
                foreach ($peerData as $year => $yearData) {
                    foreach ($yearData as $peerId => $datum) {
                        if (!in_array($peerId, $peerIds)) {
                            $peerIds[] = $peerId;
                        }
                    }
                }

                $data = $peerData;
            }



            $newData[$dbColumn] = $data;
        }

        return array($newData, $peerIds);
    }

    public function getOtherData($peersData, $columnToNotGet)
    {
        $clone = $peersData;
        unset($clone[$columnToNotGet]);

        foreach ($clone as $dbColumn => $otherData) {
            //prd($otherData);
            return $otherData;
        }
    }

    public function getPeerData($peerGroup, $dbColumn, $year)
    {
        $data = array();
        foreach ($peerGroup->getPeers() as $collegeId) {
            $college = $this->getCollegeModel()->find($collegeId);

            if ($ob = $college->getObservationForYear($year)) {
                $datum = $ob->get($dbColumn);
                if (null !== $datum) {
                    $data[$college->getId()] = floatval($datum);
                }
            }
        }

        return $data;
    }

    public function makePeerCohort($peersData, $otherData = null)
    {
        //pr($otherData);

        $minPeers = 5;

        // Step one: sort the array to start at the most recent year
        krsort($peersData);

        $cohort = null;
        $updatedCohort = array();
        $firstYear = null;
        foreach ($peersData as $year => $yearData) {
            // Step two: grab the most recent year and use that as the starting cohort
            if ($cohort === null) {
                $cohort = array_keys($yearData);
            }

            // Step three: loop over peer data and reduce the cohort
            $updatedCohort = array();
            foreach ($cohort as $collegeId) {
                if (isset($yearData[$collegeId])) {
                    //pr($yearData[$collegeId]); pr($otherData[$year][$collegeId]);
                    //pr($yearData); pr($otherData[$year]);

                    if (empty($otherData) || isset($otherData[$year][$collegeId])) {
                        $updatedCohort[] = $collegeId;
                        //pr($year); pr($updatedCohort);
                    }

                }
            }

            if (count($updatedCohort) < $minPeers) {
                $firstYear = $year + 1;
                break;
            } else {
                $cohort = $updatedCohort;
                $firstYear = $year;
            }
        }

        // Step four: rebuild the data using only the cohort colleges
        $newPeerData = $this->rebuildDataForCohort($peersData, $cohort, $firstYear);

        // Step five: flip the array back around so it starts with the lowest year
        ksort($newPeerData);

        return array($newPeerData, $cohort);
    }

    public function rebuildDataForCohort($peersData, $cohort, $firstYear)
    {
        $newPeerData = array();
        foreach ($peersData as $year => $yearData) {
            if ($year < $firstYear) {
                continue;
            }

            $newYearData = array();
            foreach ($cohort as $collegeId) {
                $newYearData[$collegeId] = $yearData[$collegeId];
            }

            $newPeerData[$year] = $newYearData;
        }

        return $newPeerData;
    }
}
