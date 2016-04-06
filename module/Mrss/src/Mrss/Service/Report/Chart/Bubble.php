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

        //$config['yAxis']['useHTML'] = true;
        $config['yAxis']['title']['style'] = array(
            'width' => '300px',
        );

        $this->setConfig($config);
    }

    public function getPointFormat()
    {
        $xLabel = $this->getXLabel();
        $yLabel = $this->getYLabel();


        $xFormat = $this->getXFormat();
        $yFormat = $this->getYFormat();


        $pointFormat = "<strong>{point.name}</strong><br><strong>$xLabel:</strong> {point.x}<br> <strong>$yLabel</strong>: {point.y}";
        $pointFormat = str_replace('{point.x}', str_replace('y', 'point.x', $xFormat), $pointFormat);
        $pointFormat = str_replace('{point.y}', str_replace('y', 'point.y', $yFormat), $pointFormat);


        if ($zFormat = $this->getZFormat()) {
            $zLabel = $this->getZLabel();
            $zFormat = str_replace('y', 'point.z', $zFormat);

            $pointFormat .= "<br> <strong>$zLabel:</strong> $zFormat";
        }


        return $pointFormat;
    }

    public function updateAllFormats()
    {
        // Only do this when both formats are loaded
        if ($this->getXFormat() && $this->getYFormat()) {
            $config = $this->getConfig();

            // Tooltip
            $config['plotOptions']['scatter']['tooltip']['pointFormat'] = $this->getPointFormat();
            $config['plotOptions']['bubble']['tooltip']['pointFormat'] = $this->getPointFormat();

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

        $this->wrapYAxisTitle();
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

        return $this;
    }

    public function setRegression($regression)
    {
        $config = $this->getConfig();

        $regression = !empty($regression);

        $config['series'][0]['regression'] = $regression;
        $config['series'][0]['regressionSettings'] = array(
            'type' => 'linear',
            'color' => 'rgba(0, 0, 0, .9)'
        );

        $this->setConfig($config);

        return $this;
    }

    public function setZLabel($label)
    {
        return $this->setLabel($label, $this->zKey);
        /*$config = $this->getConfig();

        $config['zLabel'] = $label;
        $config['series'][0]['zLabel'] = $label;

        return $config;*/
    }
}
