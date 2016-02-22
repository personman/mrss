<?php

namespace Mrss\Service\Report\ChartBuilder;

use Mrss\Service\Report\ChartBuilder;
use Mrss\Service\Report\Chart\Bubble;
use Mrss\Service\Report\Chart\ScatterPlot;
use Mrss\Service\Report\Calculator;

class BubbleBuilder extends ChartBuilder
{
    public function getChart()
    {
        $config = $this->getConfig();

        //$this->setConfig($config);

        $year = $this->getYear();
        //pr($year);
        $benchmark1 = $x = $config['benchmark1'];
        $benchmark2 = $y = $config['benchmark2'];
        $size = $config['benchmark3'];
        $title = $config['title'];

        $subtitle = null;
        if (!empty($config['subtitle'])) {
            $subtitle = $config['subtitle'];
        }

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

        $benchmarkGroupIds = array();

        // Fetch the benchmark configs
        $xBenchmark = $this->getBenchmarkModel()->findOneByDbColumn($x);
        $yBenchmark = $this->getBenchmarkModel()->findOneByDbColumn($y);

        $benchmarkGroupIds[] = $xBenchmark->getBenchmarkGroup()->getId();
        $benchmarkGroupIds[] = $yBenchmark->getBenchmarkGroup()->getId();

        if ($size) {
            $zBenchmark = $this->getBenchmarkModel()->findOneByDbColumn($size);
            $benchmarkGroupIds[] = $zBenchmark->getBenchmarkGroup()->getId();
        }


        $subscriptions = $this->getSubscriptionModel()
            ->findWithPartialObservations($study, $year, $dbColumns, true, true, $benchmarkGroupIds);

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
        $includedPeers = array();

        if ($peerGroup) {
            $peerGroup = $this->getPeerGroupModel()->find($peerGroup);
            if ($peerGroup) {
                $peerIds = $peerGroup->getPeers();
            }
        }

        $subCount = 0;
        foreach ($subscriptions as $subscription) {
            //pr($subscription->getCollege()->getId());
            //pr($collegeId);

            $observation = $subscription->getObservation();
            $college = $subscription->getCollege();
            //unset($subscription);

            $xVal = $observation->get($x);
            $yVal = $observation->get($y);

            $test = array($xVal, $yVal);
            //pr($test);

            if ($size) {
                $sizeVal = $observation->get($size);
            } else {
                $sizeVal = 1;
            }


            if ($xVal && $yVal && $sizeVal) {
                $subCount++;

                $datum = array(
                    floatval($xVal),
                    floatval($yVal),
                    floatval($sizeVal)
                );

                // Highlight the college?
                $hideMine = !empty($config['hideMine']);
                if (!$hideMine && $college->getId() == $collegeId) {
                    $yourCollege[] = $datum;
                } elseif (!empty($peerIds) && in_array($college->getId(), $peerIds)) {
                    $includedPeers[] = $college->getName();
                    $peerData[] = $datum;
                } else {
                    $data[] = $datum;
                }

                // Save 'em for the median
                $xvals[] = $xVal;
                $yVals[] = $yVal;
            }

            unset($observation);
        }

        $xLabel = $xBenchmark->getDescriptiveReportLabel();
        $yLabel = $yBenchmark->getDescriptiveReportLabel();

        if ($definition = $xBenchmark->getReportDescription(true)) {
            $this->addFootnote("$xLabel: " . $definition);
        }

        if ($definition = $yBenchmark->getReportDescription(true)) {
            $this->addFootnote("$yLabel: " . $definition);
        }

        if (!empty($zBenchmark)) {
            $zLabel = $zBenchmark->getDescriptiveReportLabel();
            $definition = $zBenchmark->getReportDescription(true);

            if (empty($definition)) {
                $definition = 'Bubble size.';
            }

            $this->addFootnote("$zLabel: $definition");
        }


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
            $peerGroupName = $peerGroup->getName();

            $series[] = array(
                'name' => $peerGroupName,
                'type' => $type,
                'color' => $this->getPeerColor(),
                'data' => $peerData,
            );

            sort($includedPeers);
            $peerNames = implode(', ', $includedPeers);
            $this->addFootnote("$peerGroupName: $peerNames.");
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
            ->setSubtitle($subtitle)
            ->setSeries($series)
            ->setXFormat($xFormat)
            ->setYFormat($yFormat)
            ->setXLabel($xLabel)
            ->setYLabel($yLabel)
            ->addMedianLines($xMedian, $yMedian);

        if (!empty($zBenchmark)) {
            $sizeLabel = $zBenchmark->getDescriptiveReportLabel();
            $bubbleChart->setZLabel($sizeLabel);
        }


        $chart = $bubbleChart->getConfig();

        return $chart;
    }
}
