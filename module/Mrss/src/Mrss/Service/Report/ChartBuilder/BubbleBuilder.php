<?php

namespace Mrss\Service\Report\ChartBuilder;

use Mrss\Service\Report\ChartBuilder;
use Mrss\Service\Report\Chart\Bubble;
use Mrss\Service\Report\Chart\ScatterPlot;
use Mrss\Service\Report\Calculator;

class BubbleBuilder extends ChartBuilder
{
    public function getChart($config)
    {
        $this->setConfig($config);

        $year = $this->getYear();
        $benchmark1 = $x = $config['benchmark1'];
        $benchmark2 = $y = $config['benchmark2'];
        $size = $config['benchmark3'];
        $title = $config['title'];
        //$regression = $config['regression'];
        $peerGroup = $config['peerGroup'];
        $collegeId = $this->getCollege()->getId();


        $type = 'bubble';
        if ($config['presentation'] == 'scatter') {
            $type = 'scatter';
            $size = null;
        }

        $study = $this->getStudy();
        $dbColumns = array($x, $y);
        if (!empty($size)) {
            $dbColumns[] = $size;
        }

        $subscriptions = $this->getSubscriptionModel()
            ->findWithPartialObservations($study, $year, $dbColumns);

        $xBenchmark = $this->getBenchmarkModel()->findOneByDbColumn($x);
        $yBenchmark = $this->getBenchmarkModel()->findOneByDbColumn($y);

        $xFormat = $this->getFormat($xBenchmark);
        $yFormat = $this->getFormat($yBenchmark);

        if (!$collegeId) {
            $collegeId = $this->getCollege()->getId();
        }

        $data = array();
        $yourCollege = array();
        $peerData = array();
        $xvals = array();
        $yVals = array();

        if ($peerGroup) {
            $peerGroup = $this->getPeerGroupModel()->find($peerGroup);
            if ($peerGroup) {
                $peerIds = $peerGroup->getPeers();
            }
        }

        foreach ($subscriptions as $subscription) {
            //pr($subscription->getCollege()->getId());
            //pr($collegeId);

            $observation = $subscription->getObservation();

            $xVal = $observation->get($x);
            $yVal = $observation->get($y);

            if ($size) {
                $sizeVal = $observation->get($size);
            } else {
                $sizeVal = 1;
            }


            if ($xVal && $yVal && $sizeVal) {

                $datum = array(
                    floatval($xVal),
                    floatval($yVal),
                    floatval($sizeVal)
                );

                // Highlight the college?
                $hideMine = !empty($config['hideMine']);
                if (!$hideMine && $subscription->getCollege()->getId() == $collegeId) {
                    $yourCollege[] = $datum;
                } elseif (!empty($peerIds) && in_array($subscription->getCollege()->getId(), $peerIds)) {
                    $peerData[] = $datum;
                } else {
                    $data[] = $datum;
                }

                // Save 'em for the median
                $xvals[] = $xVal;
                $yVals[] = $yVal;
            }
        }

        $xLabel = $xBenchmark->getDescriptiveReportLabel();
        $yLabel = $yBenchmark->getDescriptiveReportLabel();


        $series = array();

        if (empty($config['hideNational'])) {
            $series[] = array(
                'type' => $type,
                'name' => 'Institutions',
                'color' => $this->getNationalColor(),
                'data' => $data,
            );
        }

        // Highlight a college?
        if (count($yourCollege)) {
            $series[] = array(
                'name' => $this->getCollege()->getName(),
                'type' => $type,
                'color' => $this->getYourColor(),
                'data' => $yourCollege,
                'marker' => array(
                    'radius' => 8
                )
            );
        }

        if (count($peerData)) {
            $series[] = array(
                'name' => $peerGroup->getName(),
                'type' => $type,
                'color' => $this->getPeerColor(),
                'data' => $peerData,
            );
        }

        // Show median lines
        $calculatorX = new Calculator($xvals);
        $calculatorY = new Calculator($yVals);

        $xMedian = $calculatorX->getMedian();
        $yMedian = $calculatorY->getMedian();

        if (empty($title)) {
            $title = '';
        }

        // Now build the chart the new way
        if ($size == null) {
            $bubbleChart = new ScatterPlot;
        } else {
            $bubbleChart = new Bubble;
        }

        $bubbleChart->setTitle($title)
            ->setSeries($series)
            ->setXFormat($xFormat)
            ->setYFormat($yFormat)
            ->setXLabel($xLabel)
            ->setYLabel($yLabel)
            ->addMedianLines($xMedian, $yMedian);

        $chart = $bubbleChart->getConfig();

        return $chart;
    }
}
