<?php

namespace Mrss\Service\Report\Chart;

class Gauge extends AbstractChart
{
    public function __construct($id, $completion)
    {
        $gauge = array(
            'chart' => array(
                'type' => 'solidgauge',
                'backgroundColor' => '#F7F7F7',
                'height' => 150
            ),
            'title' => '',
            'id' => 'systemCompletion' . $id,
            'series' => array(
                array(
                    'type' => 'solidgauge',
                    'name' => '',
                    'data' => array($completion),
                    'dataLabels' => array(
                        'format' =>  '<div style="text-align:center"><span style="font-size:25px;color: black">{y}%</span><br/>'
                    )
                )
            ),
            'pane' => array(
                'startAngle' => -90,
                'endAngle' => 90,
                'center' => array(
                    '50%',
                    '90%'
                ),
                'size' => '180%',
                'background' => array(
                    'innerRadius' => '60%',
                    'outerRadius' => '100%',
                    'shape' => 'arc'
                )
            ),
            'tooltip' => array(
                'enabled' => false
            ),
            'yAxis' => array(
                'min' => 0,
                'max' => 100,
                'minorTickInterval' => null,
                'tickAmount' => 2,
                'labels' => array(
                    'y' => 16
                ),
                'stops' => array(
                    array(0.0, '#cc181e'),
                    array(0.4999999, '#cc181e'),
                    array(0.5, '#fbb41e'),
                    array(0.7499999, '#fbb41e'),
                    array(0.75, '#5fa80b'),
                    array(1, '#5fa80b'),
                )
            ),
            'credits' => array(
                'enabled' => false
            ),
            'plotOptions' => array(
                'solidgauge' => array(
                    'dataLabels' => array(
                        'y' => 20,
                        'borderWidth' => 0
                    )
                )
            ),
            'exporting' => array(
                'enabled' => false
            )
        );

        $this->setConfig($gauge);
    }
}
