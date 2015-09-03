<?php

namespace Mrss\Service\Report\Chart;

class Bar extends AbstractChart
{
    public function __construct()
    {
        parent::__construct();

        $config = $this->getConfig();

        $config['chart']['type'] = 'column';

        $config['plotOptions']['series'] = array('animation' => false);
        $config['legend'] = array('enabled' => false);

        $config['yAxis'] = array(
            'title' => false,
            'gridLineWidth' => 0,
            'labels' => array(
                'format' => '' // Placeholder until the format is set
            )
        );

        $config['exporting']['buttons'] = array(
            'printButton' => array('enabled' => false),
            'exportButton' => array('enabled' => false)
        );

        $config['tooltip'] = array(
            'pointFormat' => ''
        );

        $config['exporting']['buttons']['contextButton']['menuItems'] = array(
            array(
                'text' => 'test'
            )
        );

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
}
