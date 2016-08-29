<?php

namespace Mrss\Service\Report\ChartBuilder;

use Mrss\Service\Report\ChartBuilder;
use Mrss\Service\Report\Chart\Line;
use Mrss\Service\Report\Calculator;

class LineBuilder extends ChartBuilder
{
    public function getChart()
    {
        $config = $this->getConfig();

        $dbColumn = $config['benchmark2'];
        $peerGroup = $config['peerGroup'];

        $benchmark = $this->getBenchmark($dbColumn);

        if ($definition = $benchmark->getReportDescription(true)) {
            $this->addFootnote($benchmark->getDescriptiveReportLabel() . ': ' . $definition);
        }

        $title = $config['title'];

        $subtitle = null;
        if (!empty($config['subtitle'])) {
            $subtitle = $config['subtitle'];
        }


        if (empty($title)) {
            $title = $benchmark->getDescriptiveReportLabel();
        }









        // Get the college's reported data
        $data = $this->getDataForCollege($dbColumn);


        // Get the median
        $percentiles = $config['percentiles'];
        //$percentiles = array(50);

        $mediansData = array();
        $peerMediansData = array();
        foreach ($percentiles as $percentile) {
            $medians = $this->getPercentileModel()->findByBenchmarkAndPercentile($benchmark, $percentile);

            $medianData = array();
            foreach ($medians as $median) {
                // Don't show any data for years they didn't subscribe
                if (!isset($data[$median->getYear()])) {
                    continue;
                }
                $medianData[$median->getYear()] = floatval($median->getValue());
            }
            ksort($medianData);

            list($data, $medianData) = $this->syncArrays($data, $medianData);

            // Don't show the current year if reports aren't open yet
            if (!$this->getStudy()->getReportsOpen()) {
                $year = $this->getStudy()->getCurrentYear();
                unset($data[$year]);
                unset($medianData[$year]);
            }


            // Peer group median
            $peerMedianData = array();
            if (!empty($peerGroup)) {
                $peerGroupModel = $this->getPeerGroupModel();
                $peerGroup = $peerGroupModel->find($peerGroup);

                if ($peerGroup) {
                    list($peerMedianData, $peerIds) = $this->getPeerMedians(
                        $peerGroup,
                        $dbColumn,
                        array_keys($medianData),
                        $percentile
                    );

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


                }
            }

            list($data, $medianData, $peerMedians) = $this->fillInGaps($data, $medianData, $peerMedianData);



            $mediansData[$percentile] = $medianData;
            $peerMediansData[$percentile] = $peerMedianData;
        }

        if (!empty($peerFootnote)) {
            $this->addFootnote($peerGroup->getName() . ': ' . $peerFootnote);
        }




        // Build the series
        $series = array();

        if (empty($config['hideMine'])) {
            $series[] = array(
                'name' => $this->getCollege()->getName(),
                'data' => array_values($data),
                'color' => $this->getYourColor()
            );
        }

        if (empty($config['hideNational'])) {
            foreach ($mediansData as $percentile => $medianData) {
                if ($percentile == 50) {
                    $label = 'Median';
                } else {
                    $label = $this->getOrdinal($percentile);
                }
                $series[] = array(
                    'name' => "National $label",
                    'data' => array_values($medianData),
                    'color' => $this->getNationalColor()
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
                    'color' => $this->getPeerColor()
                );
            }
        }

        // Multiple trend lines?
        if (!empty($config['multiTrend'])) {
            $series[] = array(
                'name' => 'test',
                'data' => array(null, null, null, null, null, null, 5, 6, 25, 50),
                'color' => '#FF0000'
            );

        }

        $xCategories = $this->offsetYears(array_keys($data), $benchmark->getYearOffset());

        $chart = new Line;
        $chart->setTitle($title)
            ->setSubtitle($subtitle)
            ->setYLabel($benchmark->getDescriptiveReportLabel())
            ->setYFormat($this->getFormat($benchmark))
            ->setCategories($xCategories)
            ->setSeries($series);

        // Percentages should have the axis as 0-100
        if ($benchmark->isPercent()) {
            $chart->setYAxisMax(100);
            $chart->setYAxisMin(0);
        }


        return $chart->getConfig();
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

            $data[$subscription->getYear()] = floatval($subscription->getObservation()->get($dbColumn));
        }
        ksort($data);

        return $data;
    }

    public function syncArrays($array1, $array2)
    {
        $keys = array_unique(array_merge(array_keys($array1), array_keys($array2)));
        ksort($keys);

        $new1 = array();
        $new2 = array();
        foreach ($keys as $key) {
            if (!empty($array1[$key])) {
                $new1[$key] = $array1[$key];
            } else {
                $new1[$key] = null;
            }

            if (!empty($array2[$key])) {
                $new2[$key] = $array2[$key];
            } else {
                $new2[$key] = null;
            }
        }

        return array($new1, $new2);
    }

    protected function fillInGaps($data, $medianData, $peerMedians)
    {
        reset($data);
        $start = key($data);
        end($data);
        $end = key($data);

        $years = range($start, $end);

        $newData = $newMedians = $newPeerMedians = array();
        foreach ($years as $year) {
            if (isset($data[$year])) {
                $newData[$year] = $data[$year];
            } else {
                $newData[$year] = null;
            }

            if (isset($medianData[$year])) {
                $newMedians[$year] = $medianData[$year];
            } else {
                $newMedians[$year] = null;
            }

            if (isset($peerMedians[$year])) {
                $newPeerMedians[$year] = $peerMedians[$year];
            } else {
                $newPeerMedians[$year] = null;
            }
        }

        return array($newData, $newMedians, $newPeerMedians);
    }

    protected function getPeerMedians($peerGroup, $dbColumn, $years, $breakpoint = 50)
    {
        $peersData = array();

        foreach ($years as $year) {
            $peersData[$year] = $this->getPeerData($peerGroup, $dbColumn, $year);
        }

        list($peersData, $collegeIds) = $this->makePeerCohort($peersData);

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

    public function makePeerCohort($peersData)
    {
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
                    $updatedCohort[] = $collegeId;
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

        // Step five: flip the array back around so it starts with the lowest year
        ksort($newPeerData);

        return array($newPeerData, $cohort);
    }
}
