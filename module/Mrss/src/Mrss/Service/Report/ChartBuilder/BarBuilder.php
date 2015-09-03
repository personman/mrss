<?php

namespace Mrss\Service\Report\ChartBuilder;

use Mrss\Service\Report\ChartBuilder;
use Mrss\Service\Report\Chart\Bar;

class BarBuilder extends ChartBuilder
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

        $chartXCategories =$this->getPercentileBreakPointLabels();

        // @todo
        $series = array();
        $percentiles = $this->getPercentileModel()
            ->findByBenchmarkAndYear($xBenchmark, $this->getYear());
        $percentileData = array();
        foreach ($percentiles as /** var Percentile */ $percentile) {
            if ($percentile->getPercentile() == 'N') {
                continue;
            }

            $percentileData[$percentile->getPercentile()] = $percentile
                ->getValue();
        }

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

    public function getReportedValue($dbColumn)
    {
        $observation = $this->getObservationModel()->findOne(
            $this->getCollege()->getId(),
            $this->getYear()
        );

        $value = null;
        if ($observation) {
            $value = $observation->get($dbColumn);
        }

        return $value;
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
