<?php

namespace Mrss\Service\Report\ChartBuilder;

use Mrss\Service\Report\ChartBuilder;
use Mrss\Service\Report\Chart\Line;

class LineBuilder extends ChartBuilder
{
    public function getChart($config)
    {
        $this->setConfig($config);

        $dbColumn = $config['benchmark2'];
        $peerGroup = $config['peerGroup'];

        $benchmark = $this->getBenchmark($dbColumn);

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
        if ($peerGroup) {
            $peerGroupModel = $this->getPeerGroupModel();
            $peerGroup = $peerGroupModel->find($peerGroup);

            list($peerMedians, $peerIds) = $this->getPeerMedians($peerGroup, $dbColumn, array_keys($medianData));
        }

        // @todo: add footnote listing peers using $peerIds

        // Build the series
        $series = array();
        $series[] = array(
            'name' => $this->getCollege()->getName(),
            'data' => array_values($data)
        );

        $series[] = array(
            'name' => 'National Median',
            'data' => array_values($medianData)
        );

        if (!empty($peerMedians)) {
            $series[] = array(
                'name' => $peerGroup->getName() . ' Median',
                'data' => array_values($peerMedians)
            );
        }

        $xCategories = $this->offsetYears(array_keys($data), $benchmark->getYearOffset());

        $chart = new Line;
        $chart->setTitle($title)
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
}
