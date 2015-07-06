<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;

class ChartBuilder extends Report
{
    protected $config;

    protected $year;

    protected $peers;

    protected $minimumPeers = 5;

    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function setPeers($peers)
    {
        $this->peers = $peers;
    }

    public function getPeers()
    {
        return $this->peers;
    }

    public function getNationalColor()
    {
        return '#0065A1';
    }

    public function getYourColor()
    {
        return '#9CBF3D';
    }

    public function getPeerColor()
    {
        //return '#422D68';
        return '#75609B';
    }
}
