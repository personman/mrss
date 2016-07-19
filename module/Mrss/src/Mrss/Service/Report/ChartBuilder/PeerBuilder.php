<?php

namespace Mrss\Service\Report\ChartBuilder;

use Mrss\Service\Report\ChartBuilder;
use Mrss\Service\Report\Chart\Bar;

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
    public function getChart()
    {
        $config = $this->getConfig();

        $x = $config['benchmark1'];


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
        $percentileData[] = $reportedValue;
        $chartXCategories[] = $this->getYourCollegeLabel();

        $chartValues = array_combine($chartXCategories, $percentileData);
        asort($chartValues);
        $chartXCategories = array_keys($chartValues);

        $series = $this->buildSeries($chartValues, $xBenchmark);

        if ($definition = $xBenchmark->getReportDescription(true)) {
            $this->addFootnote("$xLabel: " . $definition);
        }

        $barChart = new Bar;
        $barChart->setTitle($title)
            ->setSubtitle($subtitle)
            ->setSeries($series)
            ->setXFormat($xFormat)
            ->setXLabel($xLabel)
            ->setCategories($chartXCategories);

        return $barChart->getConfig();
    }

    public function buildSeries($chartValues, $benchmark)
    {
        $roundTo = $this->getDecimalPlaces($benchmark);
        $format = $this->getFormat($benchmark);

        $chartData = array();
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
}
