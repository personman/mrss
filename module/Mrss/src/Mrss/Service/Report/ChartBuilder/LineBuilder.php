<?php

namespace Mrss\Service\Report\ChartBuilder;

use Mrss\Entity\System;
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
        $years = $this->years;

        $config = $this->getConfig();
        if (!empty($config['startYear']) || !empty($config['endYear'])) {
            $newYears = array();
            foreach ($years as $year) {
                if (!empty($config['startYear']) && $year < $config['startYear']) {
                    continue;
                }
                if (!empty($config['endYear']) && $year > $config['endYear']) {
                    continue;
                }

                $newYears[] = $year;
            }

            $years = $newYears;
        }

        return $years;
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



        //$xCategories = $this->offsetYears(array_keys($data), $benchmark->getYearOffset());
        $xCategories = $this->offsetYears($this->getYears(), $benchmark->getYearOffset());
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
            ->setSeries($series)
            ->setWidth($this->getWidthSetting());

        // Percentages should have the axis as 0-100
        $forceScale = $this->getStudyConfig()->percent_chart_scale_1_100;
        if ($benchmark->isPercent() && empty($config['percentScaleZoom']) && $forceScale) {
            $chart->setYAxisMax(100);
            $chart->setYAxisMin($this->getYMin($series));
        }


        return $chart->getConfig();
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

        $peerGroup = null;
        if ($peerGroupConfig) {
            $peerGroup = $peerGroupModel->find($peerGroupConfig);
        }


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
            $peerMeansData = array();

            if (count($percentiles)) {
                foreach ($percentiles as $percentile) {
                    $medianData = $this->getMedianData($benchmark, $percentile);

                    list($peerMedianData, $peerIds, $peerMeansData) = $this->getPeerMedianData(
                        $peerGroup,
                        $benchmark,
                        $percentile
                    );

                    $mediansData[$percentile] = $medianData;
                    $peerMediansData[$percentile] = $peerMedianData;
                }
            } else {
                list($peerMedianData, $peerIds, $peerMeansData) = $this->getPeerMedianData(
                    $peerGroup,
                    $benchmark,
                    50
                );
            }


            $allData[$dbColumn] = array(
                'data' => $data,
                'mediansData' => $mediansData,
                'peerMediansData' => $peerMediansData,
                'peerIds' => $peerIds,
                'peerMeansData' => $peerMeansData
            );
        }

        return $allData;
    }

    protected function getSeriesName($name, $dbColumn)
    {
        $config = $this->getConfig();

        if (!empty($config['multiTrend'])) {
            $benchmark = $this->getBenchmark($dbColumn);
            //$name .= '|' . $benchmark->getDescriptiveReportLabel();
            $name .= ' - ' . $benchmark->getDescriptiveReportLabel();
        }

        return $name;
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
            $peerMeansData = $dataForBenchmark['peerMeansData'];

            if (empty($config['hideMine'])) {
                $name = $this->getSeriesName($this->getCollege()->getNameAndState(), $dbColumn);

                $series[] = array(
                    'name' => $name,
                    'data' => array_values($data),
                    'color' => $this->getYourColor($i)
                );
            }

            if (empty($config['hideNational'])) {
                $i = 1;
                foreach ($mediansData as $percentile => $medianData) {
                    if ($percentile == 50) {
                        $label = 'Median';
                    } else {
                        $label = $this->getOrdinal($percentile, true);
                    }

                    $nationalOrNetwork = 'National';
                    if (isset($config['system'])) {
                        $system = $this->getSystemModel()->find($config['system']);
                        $nationalOrNetwork = $system->getName();
                    }

                    $nationalLabel = "$nationalOrNetwork $label";
                    $lighten = $i * 5;

                    $series[] = array(
                        'name' => $this->getSeriesName($nationalLabel, $dbColumn),
                        'data' => array_values($medianData),
                        'color' => $this->getNationalColor($i, $lighten)
                    );

                    $i++;
                }
            }


            if (!empty($peerGroup) && !empty($peerIds) && count($peerIds) >= $this->minimumPeers) {
                $i = 1;
                foreach ($peerMediansData as $percentile => $peerMedianData) {
                    if ($percentile == 50) {
                        $label = 'Median';
                    } else {
                        $label = $this->getOrdinal($percentile);
                    }

                    $lighten = $i * 5;

                    $label = $peerGroup->getName() . ' ' . $label;
                    $series[] = array(
                        'name' => $this->getSeriesName($label, $dbColumn),
                        'data' => array_values($peerMedianData),
                        'color' => $this->getPeerColor($i, $lighten)
                    );

                    $i++;
                }

                $config = $this->getConfig();

                if (!empty($config['colleges'])) {
                    foreach ($config['colleges'] as $collegeId) {
                        $college = $this->getCollegeModel()->find($collegeId);
                        $years = $this->getYears();
                        $data = $this->getDataForCollege($dbColumn, $college, $years);

                        $series[] = array(
                            'name' => $this->getSeriesName($college->getNameAndState(), $dbColumn),
                            'data' => array_values($data),
                        );
                    }
                }
            }


            if (!empty($peerGroup) && !empty($config['peerGroupAverage']) && !empty($peerMeansData)) {
                $series[] = array(
                    'name' => $this->getSeriesName($peerGroup->getName() . ' Average', $dbColumn),
                    'data' => array_values($peerMeansData)
                );
            }

            $i++;
        }

        return $series;
    }


    public function getDataForCollege($dbColumn, $college = null, $years = null)
    {
        if (null === $college) {
            $college = $this->getCollege();
        }

        $benchmark = $this->getBenchmark($dbColumn);

        // Get the college's reported data
        $subscriptions = $college->getSubscriptionsForStudy($this->getStudy(), false, $this->getSystem());

        $data = array();
        foreach ($subscriptions as $subscription) {
            // Skip current year if reporting isn't open yet.
            if ($this->getStudy()->getCurrentYear() == $subscription->getYear()
                && !$this->getStudy()->getReportsOpen()) {
                continue;
            }

            // Skip years not includes, if any
            if ($years && !in_array($subscription->getYear(), $years)) {
                continue;
            }

            //pr($benchmark->getYearsAvailable());
            // Skip if the benchmark isn't even available for the year
            if (!$this->showYear($subscription->getYear())) {
                continue;
            }

            $value = $subscription->getObservation()->get($dbColumn);
            if ($value !== null) {
                $value = floatval($value);
            }

            $data[$subscription->getYear()] = $value;
        }
        ksort($data);

        //prd($data);

        if (!$years) {
            $this->setYears(array_keys($data));
        }

        $data = $this->fillInGaps($data);

        return $data;
    }

    /**
     * Is at least one benchmark available for the year?
     */
    protected function showYear($year)
    {
        $showYear = false;
        foreach ($this->getDbColumns() as $dbColumn) {
            $benchmark = $this->getBenchmark($dbColumn);
            if ($benchmark->isAvailableForYear($year)) {
                $showYear = true;
            }
        }

        return $showYear;
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
        $peerMedianData = $peerMeansData = array();
        $peerIds = array();
        if (!empty($peerGroup)) {
            if ($peerGroup) {
                list($peerMedianData, $peerIds, $peerMeansData) = $this->getPeerMedians(
                    $peerGroup,
                    $dbColumn,
                    $percentile
                );
            }
        }

        $peerMedianData = $this->fillInGaps($peerMedianData);
        $peerMeansData = $this->fillInGaps($peerMeansData);

        return array($peerMedianData, $peerIds, $peerMeansData);
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
        $peerMeans = array();
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

                $peerMeans[$year] = $calculator->getMean();
            }
        }

        // If the number of peers is below the minimum, don't show any peer data
        if (count($collegeIds) < $this->minimumPeers) {
            $peerMedians = array();
            $collegeIds = array();
        }

        return array($peerMedians, $collegeIds, $peerMeans);
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
