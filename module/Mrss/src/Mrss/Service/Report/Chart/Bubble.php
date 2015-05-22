<?php

namespace Mrss\Service\Report\Chart;

class Bubble extends AbstractChart
{
    public function __construct()
    {
        parent::__construct();

        $config = $this->getConfig();

        $config['chart']['type'] = 'bubble';
        $config['chart']['zoomType'] = 'xy';

        $config['plotOptions']['scatter'] = array(
            'tooltip' => array(
                'valueDecimals' => 0,
            )
        );

        $config['xAxis'] = $this->getAxis();
        $config['yAxis'] = $this->getAxis();

        $this->setConfig($config);
    }

    public function getAxis()
    {
        $axis = array(
            'title' => array(
                'enabled' => true,
                //'text' => $xLabel
            ),
            'labels' => array(),
            'plotLines' => array()
        );

        return $axis;
    }

    public function getPointFormat()
    {
        $xLabel = $this->getXLabel();
        $yLabel = $this->getYLabel();

        $xFormat = $this->getXFormat();
        $yFormat = $this->getYFormat();

        $pointFormat = "<strong>$xLabel:</strong> {point.x}<br> <strong>$yLabel</strong>: {point.y}";
        $pointFormat = str_replace('{point.x}', str_replace('y', 'point.x', $xFormat), $pointFormat);
        $pointFormat = str_replace('{point.y}', str_replace('y', 'point.y', $yFormat), $pointFormat);

        return $pointFormat;
    }

    public function updateAllFormats()
    {
        // Only do this when both formats are loaded
        if ($this->getXFormat() && $this->getYFormat()) {
            $config = $this->getConfig();

            // Tooltip
            $config['plotOptions']['scatter']['tooltip']['pointFormat'] = $this->getPointFormat();

            // X axis
            $config['xAxis']['labels']['format'] = $this->convertFormatForAxisLabel($this->getXFormat());

            // Y axis
            $config['yAxis']['labels']['format'] = $this->convertFormatForAxisLabel($this->getYFormat());

            $this->setConfig($config);
        }
    }

    public function updateAllLabels()
    {
        $config = $this->getConfig();

        $config['xAxis']['title']['text'] = $this->getXLabel();
        $config['yAxis']['title']['text'] = $this->getYLabel();

        $this->setConfig($config);
    }

    public function addMedianLines($xMedian, $yMedian)
    {
        $medianLineColor = '#CCC';

        $config = $this->getConfig();

        $config['xAxis']['plotLines'] = array(
            array(
                'color' => $medianLineColor,
                'value' => $xMedian,
                'width' => 1
            )
        );
        $config['xAxis']['gridLineWidth'] = 0;

        $config['yAxis']['plotLines'] = array(
            array(
                'color' => $medianLineColor,
                'value' => $yMedian,
                'width' => 1
            )
        );
        $config['yAxis']['gridLineWidth'] = 0;

        $this->setConfig($config);
    }
}
