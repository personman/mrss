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
        $subtitle = $config['subtitle'];

        if (empty($title)) {
            $title = $benchmark->getDescriptiveReportLabel();
        }

        // Get the college's reported data
        $subscriptions = $this->getCollege()->getSubscriptionsForStudy($this->getStudy());
        $data = array();
        foreach ($subscriptions as $subscription) {
            $data[$subscription->getYear()] = floatval($subscription->getObservation()->get($dbColumn));
        }
        ksort($data);


        // Get the median
        $percentile = 50;
        $medians = $this->getPercentileModel()->findByBenchmarkAndPercentile($benchmark, $percentile);

        $medianData = array();
        foreach ($medians as $percentile) {
            // Don't show any data for years they didn't subscribe
            if (!isset($data[$percentile->getYear()])) {
                continue;
            }
            $medianData[$percentile->getYear()] = floatval($percentile->getValue());
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
        $peerMedians = array();
        if (!empty($peerGroup)) {
            $peerGroupModel = $this->getPeerGroupModel();
            $peerGroup = $peerGroupModel->find($peerGroup);

            if ($peerGroup) {
                list($peerMedians, $peerIds) = $this->getPeerMedians($peerGroup, $dbColumn, array_keys($medianData));

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

                $this->addFootnote($peerGroup->getName() . ': ' . $peerFootnote);
            }
        }

        list($data, $medianData, $peerMedians) = $this->fillInGaps($data, $medianData, $peerMedians);

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
            $series[] = array(
                'name' => 'National Median',
                'data' => array_values($medianData),
                'color' => $this->getNationalColor()
            );
        }

        if (!empty($peerGroup) && count($peerIds) >= $this->minimumPeers) {
            $series[] = array(
                'name' => $peerGroup->getName() . ' Median',
                'data' => array_values($peerMedians),
                'color' => $this->getPeerColor()
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

        return $chart->getConfig();
    }

    protected function syncArrays($array1, $array2)
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

    protected function getPeerMedians($peerGroup, $dbColumn, $years)
    {
        $peersData = array();
        $breakpoint = 50; // Median: 50

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
