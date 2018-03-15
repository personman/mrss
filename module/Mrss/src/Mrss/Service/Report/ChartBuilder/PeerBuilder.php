<?php

namespace Mrss\Service\Report\ChartBuilder;

use Mrss\Service\Report\ChartBuilder;

/**
 * Class PeerBuilder
 *
 * Builds the chart for peer comparisons. Takes a single benchmark, a single year, a single peer group and compares
 * the data using a horizontal bar chart.
 *
 * @package Mrss\Service\Report\ChartBuilder
 */
class PeerBuilder extends BarBuilder
{
    protected $peerService;

    public function getChart()
    {
        $config = $this->getConfig();

        //$x = $config['benchmark1'];
        $year = $config['year'];

        $dbColumns = $this->getDbColumnsFromConfig();
        $benchmarks = $this->getBenchmarksFromConfig();

        //pr($config);
        //$xBenchmark = $this->getBenchmarkModel()->findOneByDbColumn($x);

        $title = $config['title'];
        $subtitle = null;
        if (!empty($config['subtitle'])) {
            $subtitle = $config['subtitle'];
        }

        $allChartValues = array();
        $i = 0;
        foreach ($dbColumns as $dbColumn) {
            $peerData = array();
            $xBenchmark = $this->getBenchmarkModel()->findOneByDbColumn($dbColumn);

            $reportedValue = $this->getReportedValue($dbColumn);
            $peerData[$this->getCollege()->getId()] = $reportedValue;

            // Get peer group
            $peerGroupId = $config['peerGroup'];

            $includedPeers = array();
            $peerGroupName = null;
            if ($peerGroup = $this->getPeerGroupModel()->find($peerGroupId)) {
                $peerGroupName = $peerGroup->getName();

                // Loop over peers
                foreach ($peerGroup->getPeers() as $collegeId) {
                    if ($observation = $this->getObservationModel()->findOne($collegeId, $year)) {
                        if ($college = $observation->getCollege()) {
                            if ($value = $observation->get($dbColumn)) {
                                $chartXCategories[] = 'blah';
                                $peerData[$collegeId] = $value;

                                $includedPeers[] = $college->getNameAndState();
                            }

                        }
                    }
                }
            }


            $this->getPeerService()->setCurrentCollege($this->getCollege());
            $this->getPeerService()->setYear($year);
            $sort = (count($benchmarks) <= 1);
            $chartValues = $this->getPeerService()
                ->sortAndLabelPeerData($peerData, $this->getCollege(), $xBenchmark, $sort);


            $allChartValues[] = array(
                'name' => $xBenchmark->getDescriptiveReportLabel(),
                'data' => $chartValues,
            );

            // Add footnotes
            $definition = $xBenchmark->getReportDescription(true);
            $xLabel = $xBenchmark->getDescriptiveReportLabel();
            $this->addFootnote("$xLabel: " . $definition);

            $i++;
        }






        if (!empty($includedPeers)) {
            sort($includedPeers);
            $peerNames = implode(', ', $includedPeers);
            $this->addFootnote("$peerGroupName: $peerNames.");
        }

        // Are there enough peers?
        if ($this->getStudyConfig()->anonymous_peers) {
            $minPeers = 5;
            if (count($includedPeers) < $minPeers) {
                $chartValues = array();
                $error = "Not enough peers to display data: Select peer group with at least $minPeers with data.";
                $this->addError($error);
            }

        }

        //pr($allChartValues);

        $allChartValues = $this->fillInGaps($allChartValues);

        $percentScaleZoom = $config['percentScaleZoom'];

        $chart = $this->getPeerService()
            ->getPeerBarChart($benchmarks, $allChartValues, $title, $subtitle, $this->getWidthSetting(), $percentScaleZoom);

        return $chart;
    }

    protected function fillInGaps($array)
    {
        // Find all colleges with data
        $colleges = array();
        foreach ($array as $key => $series) {
            foreach ($series['data'] as $collegeId => $datum) {
                $colleges[$collegeId] = $datum['label'];
            }
        }

        // Now the order is set
        // Loop over the colleges for each series
        $newArray = array();
        foreach ($array as $key => $series) {
            $newArray[$key] = $series;
            unset($newArray[$key]['data']);

            foreach ($colleges as $collegeId => $label) {
                $newDatum = array();
                if (!empty($series['data'][$collegeId])) {
                    $newDatum = $series['data'][$collegeId];
                } else {
                    $newDatum['label'] = $label;
                    $newDatum['value'] = null;
                    $newDatum['formatted'] = '';
                }

                $newArray[$key]['data'][$collegeId] = $newDatum;
            }
        }

        return $newArray;
    }

    protected function getDbColumnsFromConfig()
    {
        $config = $this->getConfig();

        $dbColumns = array();
        foreach (range('a', 'g') as $key) {
            $name = 'benchmark2' . $key;

            if (!empty($config[$name])) {
                $dbColumns[] = $config[$name];
            }
        }

        $dbColumns = array_unique($dbColumns);

        $this->selectedExtraBenchmarks = $dbColumns;

        // Add the highlighted data
        array_unshift($dbColumns, $config['benchmark2']);

        return $dbColumns;
    }

    protected function getBenchmarksFromConfig()
    {
        $benchmarks = array();
        foreach ($this->getDbColumnsFromConfig() as $dbColumn) {
            if ($benchmark = $this->getBenchmarkModel()->findOneByDbColumn($dbColumn)) {
                $benchmarks[] = $benchmark;
            }
        }

        return $benchmarks;
    }


    public function setPeerService($service)
    {
        $this->peerService = $service;

        return $this;
    }

    /**
     * @return \Mrss\Service\Report\Peer
     */
    public function getPeerService()
    {
        return $this->peerService;
    }
}
