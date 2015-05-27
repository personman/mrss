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
            'title' => array()
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

        $this->setConfig($config);
    }

    public function updateAllLabels()
    {
        $config = $this->getConfig();

        $config['yAxis']['title']['text'] = $this->getYLabel();

        $this->setConfig($config);
    }

    public function setCategories($categories)
    {
        $config = $this->getConfig();

        $config['xAxis']['categories'] = $categories;

        $this->setConfig($config);

        return $this;
    }
}
