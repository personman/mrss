<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;

class ChartBuilder extends Report
{
    protected $config;

    protected $year;

    protected $peers;

    protected $minimumPeers = 5;

    protected $college;

    protected $footnotes = array();

    protected $errors = array();

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

    public function getNationalColor($variation = false)
    {
        $color = '#0065A1';

        if ($variation) {
            $color = '#4DB2EE';
        }

        return $color;
    }

    public function getYourColor($variation = false)
    {
        $color = '#9CBF3D';

        if ($variation) {
            $color = '#CFF270';
        }

        return $color;
    }

    public function getPeerColor($variation = false)
    {
        $color = '#8F7AB5';
        if ($variation) {
            $color = '#DCC7FF';
        }

        return $color;
    }

    public function getFootnotes()
    {
        $this->substituteVariablesInFootnotes();

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

    public function getErrors()
    {
        return $this->errors;
    }

    public function addError($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    public function substituteVariablesInFootnotes()
    {
        $config = $this->getConfig();
        $year = $config['year'];

        $subbedFootnotes = array();
        foreach ($this->footnotes as $footnote) {
            $subbedFootnotes[] = $this->getVariableSubstitution()
                ->setStudyYear($year)->substitute($footnote);
        }

        $this->footnotes = $subbedFootnotes;
    }

    public function setCollege($college)
    {
        $this->college = $college;

        return $this;
    }

    public function getCollege()
    {
        return $this->college;
    }

    public function getSystemId()
    {
        $config = $this->getConfig();

        $systemId = null;
        if (isset($config['system'])) {
            $systemId = $config['system'];
        }

        return $systemId;
    }

    public function getSystem()
    {
        $systemId = $this->getSystemId();
        $system = null;
        if ($systemId) {
            $system = $this->getSystemModel()->find($systemId);
        }

        return $system;
    }

    public function getWidthSetting()
    {
        $config = $this->getConfig();
        $setting = 'half';
        if (!empty($config['width']) && $config['width'] == 'full') {
            $setting = 'full';
        }

        return $setting;
    }
}
