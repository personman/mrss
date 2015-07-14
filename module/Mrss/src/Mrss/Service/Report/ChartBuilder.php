<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;

class ChartBuilder extends Report
{
    protected $config;

    protected $year;

    protected $peers;

    protected $minimumPeers = 5;

    protected $footnotes = array();

    public function setConfig($config)
    {
        $this->setFootnotes(array());
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
        return '#8F7AB5';
    }

    public function getFootnotes()
    {
        return $this->footnotes;
    }

    public function setFootnotes($footnotes)
    {
        $this->footnotes = $footnotes;
    }

    public function addFootnote($footnote)
    {
        $this->footnotes[] = $footnote;
    }
}
