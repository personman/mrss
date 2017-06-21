<?php

namespace Mrss\Service\Report\Chart;

use Mrss\Entity\Benchmark;

abstract class AbstractChart
{
    protected $config = array();

    protected $formats = array();

    protected $labels = array();

    protected $xKey = 0;

    protected $yKey = 1;

    protected $zKey = 2;

    public function __construct()
    {
        $config = array(
            'id' => $this->getId(),
            'chart' => array(
                'type' => null,
            ),
            'title' => array(
                'text' => '',
            ),
            'subtitle' => array(
                'text' => '',
            ),
            'exporting' => array(
                'enabled' => true
            ),
            'credits' => array(
                'enabled' => false
            ),
            'plotOptions' => array(),
            'series' => array(),
            'lang' => array(
                'thousandsSep' => ','
            )
        );

        // Use this load function for all charts unless they override
        $config['chart']['events'] = array(
            'load' => 'loadChart'
        );

        $this->setConfig($config);
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function toJson()
    {
        return json_encode($this->getConfig());
    }

    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        $config = $this->config;

        return $config;
    }

    public function getId()
    {
        if (!isset($this->config['id'])) {
            $this->config['id'] = 'chart_' . uniqid();
        }

        return $this->config['id'];
    }

    public function setTitle($title)
    {
        $this->config['title']['text'] = $title;

        return $this;
    }

    public function setSubtitle($subtitle)
    {
        $this->config['subtitle']['text'] = $subtitle;

        return $this;
    }

    public function setSeries($series)
    {
        $this->config['series'] = $series;

        return $this;
    }

    public function getFormat($key = 0)
    {
        if (!empty($this->formats[$key])) {
            return $this->formats[$key];
        }
    }

    public function setFormat($format, $key = 0)
    {
        $this->formats[$key] = $format;
        $this->updateAllFormats();

        return $this;
    }

    public function getLabel($key = 0)
    {
        if (!empty($this->labels[$key])) {
            return $this->labels[$key];
        }
    }

    public function setLabel($label, $key = 0)
    {
        $this->labels[$key] = $label;
        $this->updateAllFormats();
        $this->updateAllLabels();

        return $this;
    }

    public function setXFormat($format)
    {
        return $this->setFormat($format, $this->xKey);
    }

    public function getXFormat()
    {
        return $this->getFormat($this->xKey);
    }

    public function setYFormat($format)
    {
        return $this->setFormat($format, $this->yKey);
    }

    public function setZFormat($format)
    {
        return $this->setFormat($format, $this->zKey);
    }

    public function getZFormat()
    {
        return $this->getFormat($this->zKey);
    }

    public function getYFormat()
    {
        return $this->getFormat($this->yKey);
    }

    public function setXLabel($label)
    {
        return $this->setLabel($label, $this->xKey);
    }

    public function getXLabel()
    {
        return $this->getLabel($this->xKey);
    }

    public function setYLabel($label)
    {
        return $this->setLabel($label, $this->yKey);
    }

    public function getYLabel()
    {
        $label = $this->getLabel($this->yKey);

        return $label;
    }

    public function getZLabel()
    {
        $label = $this->getLabel($this->zKey);

        return $label;
    }

    public function convertFormatForAxisLabel($format)
    {
        $search = array('y', '.2f', '.4f');
        $replace = array('value', '.0f', '.2f');

        // Don't round non-percent benchmarks with 2 decimal places
        if ($format == '{y:,.2f}') {
            $replace[1] = '.2f';
        }

        return str_replace($search, $replace, $format);
    }

    public function convertFormatForTooltip($format)
    {
        return str_replace('y', 'point.y', $format);
    }

    public function updateAllFormats()
    {

    }

    public function updateAllLabels()
    {

    }

    public function getAxis()
    {
        $axis = array(
            'title' => array(
                'enabled' => true,
                'style' => array('width' => '300px'),
            ),
            'labels' => array(),
            'plotLines' => array()
        );

        return $axis;
    }

    /**
     * If the Y axis title is long enough, add some padding/offset
     */
    protected function wrapYAxisTitle()
    {
        //return false;
        $length = strlen($this->getYLabel());

        $offsetPerLine = 100;
        $offsetPerLine = 60;
        $charactersPerLine = 50;

        $offset = (ceil($length / $charactersPerLine) - 1) * $offsetPerLine;
        if ($offset > 0) {
            $config = $this->getConfig();
            $config['yAxis']['title']['offset'] = $offset;

            $this->setConfig($config);
        }
    }

    public function setCategories($categories)
    {
        $config = $this->getConfig();

        $config['xAxis']['categories'] = $categories;

        $this->setConfig($config);

        return $this;
    }

    public function setYAxisMax($max)
    {
        $config = $this->getConfig();

        $config['yAxis']['max'] = $max;

        $this->setConfig($config);
    }

    public function setYAxisMin($min)
    {
        $config = $this->getConfig();

        $config['yAxis']['min'] = $min;

        $this->setConfig($config);
    }

    public function setWidth($widthSetting = 'half')
    {
        $config = $this->getConfig();

        if ($widthSetting == 'full') {
            $config['chart']['width'] = 1020;
            $config['chart']['height'] = 600;

            $config['legend']['layout'] = "horizontal";
            $config['legend']['width'] = 860;
            $config['legend']['itemWidth'] = 210;
            $config['legend']['itemStyle'] = array(
                'width' => '175px'
            );

        } else {
            $config['legend']['layout'] = "horizontal";
            $config['legend']['width'] = 430;
            $config['legend']['itemWidth'] = 210;
            $config['legend']['itemStyle'] = array(
                'width' => '180px'
            );

        }

        $this->setConfig($config);

        return $this;
    }
}
