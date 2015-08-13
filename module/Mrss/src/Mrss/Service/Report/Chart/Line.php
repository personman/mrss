<?php

namespace Mrss\Service\Report\Chart;

class Line extends AbstractChart
{
    public function __construct()
    {
        parent::__construct();

        $config = $this->getConfig();

        $config['chart']['type'] = 'line';

        //$config['xAxis'] = array();
        $config['yAxis'] = array(
            'title' => array(
                'style' => array(
                    'width' => '350px'
                ),
            )
        );

        $config['plotOptions'] = array(
            'line' => array(
                'marker' => array(
                    'enabled' => true
                )
            )
        );

        $this->setConfig($config);
    }

    public function updateAllFormats()
    {
        $config = $this->getConfig();

        // Y axis
        $config['yAxis']['labels']['format'] = $this->convertFormatForAxisLabel($this->getYFormat());

        // Tooltip
        $config['tooltip'] = array('pointFormat' => $this->convertFormatForTooltip($this->getYFormat()));

        $this->setConfig($config);
    }

    public function updateAllLabels()
    {
        $config = $this->getConfig();

        $config['yAxis']['title']['text'] = $this->getYLabel();

        $this->setConfig($config);

        $this->wrapYAxisTitle();
    }
}
