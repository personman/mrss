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
                    'width' => '300px'
                    //'height' => $this->getHeight()
                ),
            )
        );

        //pr($config);
        //if ($this->getWidthSetting() == 'full') {
            //$config['chart']['height'] = 900;
        //}

        $config['plotOptions'] = array(
            'line' => array(
                'dataLabels' => array(
                    'enabled' => false,
                    'format' => "{y}"
                ),
                'marker' => array(
                    'enabled' => true
                )
            )
        );

        // Legend tests
        //$config['legend']['title']['text'] = "Test Legend Title";
        //$config['legend']['useHTML'] = true;
        //$config['legend']['labelFormatter'] = "legendLabelFormatter";
        //$config['legend']['layout'] = "vertical";
        //$config['multiTrend'] = false;


        $this->setConfig($config);
    }

    public function updateAllFormats()
    {
        $config = $this->getConfig();

        // Y axis
        $config['yAxis']['labels']['format'] = $this->convertFormatForAxisLabel($this->getYFormat());
        $config['yAxis']['labels']['formatter'] = 'axisLabelFormatter';

        // Tooltip
        $config['tooltip'] = array(
            'pointFormat' => $this->convertFormatForTooltip($this->getYFormat()),
            'headerFormat' => '<span style="font-size: 10px">{series.name} {point.key}</span><br/>'
        );

        // Data label
        $config['plotOptions']['line']['dataLabels']['format'] = $this->getYFormat();

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
