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
            'exporting' => array(
                'enabled' => true
            ),
            'credits' => array(
                'enabled' => false
            ),
            'plotOptions' => array(),
            'series' => array()
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
        return $this->config;
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
        return $this->getLabel($this->yKey);
    }

    public function convertFormatForAxisLabel($format)
    {
        return str_replace(array('y', '.2f', '.4f'), array('value', '.0f', '.2f'), $format);
    }

    public function updateAllFormats()
    {

    }

    public function updateAllLabels()
    {

    }
}
