<?php

namespace Mrss\Service\Report\ChartBuilder;

use Mrss\Service\Report\ChartBuilder;
use Mrss\Service\Report\Chart\Bar;
use Mrss\Entity\College;

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
        $xFormat = $this->getFormat($xBenchmark);
        $xLabel = $xBenchmark->getDescriptiveReportLabel();

        $title = $config['title'];
        $subtitle = null;
        if (!empty($config['subtitle'])) {
            $subtitle = $config['subtitle'];
        }

        $collegeId = $this->getCollege()->getId();


        // @todo
        $series = array();
        $reportedValue = $this->getReportedValue($x);
        $peerData[$this->getCollege()->getId()] = $reportedValue;
        $chartXCategories[] = $this->getYourCollegeLabel();


        // Get peer group
        $peerGroupId = $config['peerGroup'];
        $peerGroup = $this->getPeerGroupModel()->find($peerGroupId);

        // Loop over peers
        foreach ($peerGroup->getPeers() as $collegeId) {
            $observation = $this->getObservationModel()->findOne($collegeId, $year);
            if ($value = $observation->get($x)) {
                $chartXCategories[] = 'blah';
                $peerData[$collegeId] = $value;
            }

        }

        /*$chartValues = array_combine($chartXCategories, $peerData);
        asort($chartValues);
        $chartXCategories = array_keys($chartValues);
*/

        $this->getPeerService()->setCurrentCollege($this->getCollege());
        $this->getPeerService()->setYear($year);
        $chartValues = $this->getPeerService()->sortAndLabelPeerData($peerData, $this->getCollege());
        $chartXCategories = array_keys($chartValues);
        //prd($chartValues);

        $series = $this->buildSeries($chartValues, $xBenchmark);

        if ($definition = $xBenchmark->getReportDescription(true)) {
            $this->addFootnote("$xLabel: " . $definition);
        }

        $barChart = new Bar;
        $barChart->setOrientationHorizontal();
        $barChart->setTitle($title)
            ->setSubtitle($subtitle)
            ->setSeries($series)
            ->setXFormat($xFormat)
            ->setXLabel($xLabel)
            ->setCategories($chartXCategories);

        //return $barChart->getConfig();

        return $this->getPeerService()->getPeerBarChart($xBenchmark, $chartValues);
    }

    public function buildSeries($chartValues, $benchmark)
    {
        $roundTo = $this->getDecimalPlaces($benchmark);
        $format = $this->getFormat($benchmark);

        $chartData = array();
        pr($chartValues);
        foreach ($chartValues as $i => $value) {
            $value = round($value, $roundTo);

            if (!empty($chartXCategories[$i])) {
                $label = $chartXCategories[$i];
            } else {
                $label = $i;
            }

            // Your college
            if ($i === $this->getYourCollegeLabel()) {
                $dataLabelEnabled = true;
                $color = $this->getBarChartHighlightColor();
            } else {
                $dataLabelEnabled = false;
                $color = $this->getBarChartBarColor();
            }

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

        return $series;
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
