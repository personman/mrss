<?php

namespace Mrss\Service\Report\Chart;

use Mrss\Entity\Benchmark;

abstract class AbstractChart
{
    protected $config = array();

    /**
     * Benchmarks are used to derive axis labels and formats
     *
     * @var Benchmark[] $benchmarks
     */
    protected $benchmarks = array();

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
    }

    public function setSeries($series)
    {
        $this->config['series'] = $series;
    }

    public function setBenchmarks($benchmarks)
    {
        $this->benchmarks = $benchmarks;

        return $this;
    }

    public function getBenchmarks()
    {
        return $this->benchmarks;
    }

    public function getFormat($key = 0)
    {

    }
}
