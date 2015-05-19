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
                // @todo: formatting
                //'pointFormat' => $pointFormat
            )
        );

        $config['xAxis'] = $this->getXAxis();

        $this->setConfig($config);
    }

    public function getXAxis()
    {
        $xAxis = array(
            'title' => array(
                'enabled' => true,

                //'text' => $xLabel
            ),
            'labels' => array(
                //'format' => str_replace(array('y', '.2f', '.4f'), array('value', '.0f', '.2f'), $xFormat)
            )
        );

        return $xAxis;
    }
}
