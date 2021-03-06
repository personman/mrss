<?php

namespace Mrss\Service\Report\Chart;

class Bar extends AbstractChart
{
    public function __construct()
    {
        parent::__construct();

        $config = $this->getConfig();

        $config['chart']['type'] = 'column';

        // The x-axis labels were getting cut off. This helps:
        $config['chart']['marginRight'] = 30;

        $config['plotOptions']['series'] = array(
            'animation' => false,
            //'dataLabels' => array('crop' => false)
        );

        $config['legend'] = array('enabled' => false);

        $config['yAxis'] = array(
            'title' => false,
            'gridLineWidth' => 0,
            'labels' => array(
                'format' => '', // Placeholder until the format is set
                'step' => 1
            )
        );

        $config['exporting']['buttons'] = array(
            'printButton' => array('enabled' => false),
            'exportButton' => array('enabled' => false)
        );

        $config['tooltip'] = array(
            'pointFormat' => ''
        );


        $this->setConfig($config);
    }

    public function enableLegend($enabled = true)
    {
        $config = $this->getConfig();
        $config['legend'] = array('enabled' => $enabled);
        $this->setConfig($config);
    }

    public function setStacked($stacked = true)
    {
        $config = $this->getConfig();

        if ($stacked) {
            $config['plotOptions']['series']['stacking'] = 'normal';
        } else {
            unset($config['plotOptions']['series']['stacking']);
        }

        $this->setConfig($config);
    }

    public function updateAllFormats()
    {
        $config = $this->getConfig();

        // Y axis labels
        $config['yAxis']['labels']['format'] = $this->convertFormatForAxisLabel($this->getXFormat());

        // Tooltip
        $config['tooltip']['pointFormat'] = $this->convertFormatForTooltip($this->getXFormat());

        $this->setConfig($config);
    }

    public function setOrientationHorizontal()
    {
        $config = $this->getConfig();

        $config['chart']['type'] = 'bar';

        $this->setConfig($config);

        return $this;
    }

    public function removeTickMarks()
    {
        $config = $this->getConfig();

        $config['xAxis']['tickLength'] = 0;

        $this->setConfig($config);

        return $this;
    }
}
