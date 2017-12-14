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

        $x = $config['benchmark1'];
        $year = $config['year'];

        $xBenchmark = $this->getBenchmarkModel()->findOneByDbColumn($x);

        $title = $config['title'];
        $subtitle = null;
        if (!empty($config['subtitle'])) {
            $subtitle = $config['subtitle'];
        }

        $reportedValue = $this->getReportedValue($x);
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
                    if ($value = $observation->get($x)) {
                        $chartXCategories[] = 'blah';
                        $peerData[$collegeId] = $value;

                        $includedPeers[] = $observation->getCollege()->getNameAndState();
                    }
                }
            }
        }


        $this->getPeerService()->setCurrentCollege($this->getCollege());
        $this->getPeerService()->setYear($year);
        $chartValues = $this->getPeerService()->sortAndLabelPeerData($peerData, $this->getCollege(), $xBenchmark);

        // Add footnotes
        $definition = $xBenchmark->getReportDescription(true);
        $xLabel = $xBenchmark->getDescriptiveReportLabel();
        $this->addFootnote("$xLabel: " . $definition);


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

        return $this->getPeerService()
            ->getPeerBarChart($xBenchmark, $chartValues, $title, $subtitle, $this->getWidthSetting());
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
