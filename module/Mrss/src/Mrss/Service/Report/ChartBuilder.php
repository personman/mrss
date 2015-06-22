<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;

class ChartBuilder extends Report
{
    protected $config;

    protected $year;

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
}
