<?php

namespace Mrss\Service\Report;

use Mrss\Service\Report;
use MischiefCollective\ColorJizz\Formats\Hex;

class ChartBuilder extends Report
{
    protected $config;

    protected $year;

    protected $peers;

    protected $minimumPeers = 5;

    protected $college;

    protected $footnotes = array();

    protected $errors = array();

    protected $selectedExtraBenchmarks = array();

    public function setConfig($config)
    {
        $this->setFootnotes(array());
        $this->config = $config;

        return $this;
    }

    protected function getIncludeThisCollege()
    {
        return $this->getStudyConfig()->use_structures;
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

    public function getNationalColor($variation = false, $lighten = 0)
    {
        $color = '#0065A1';

        if ($variation) {
            $color = '#4DB2EE';
        }

        if ($lighten) {
            $color = $this->adjustBrightness($color, $lighten);
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

    function adjustBrightness($hex, $steps)
    {
        $color = Hex::fromString($hex);

        $adjusted = '#' . $color->brightness($steps * -0.8)->hue($steps * 2)->toHex()->__toString();

        return $adjusted;
    }

    public function getPeerColor($variation = false, $lighten = 0)
    {
        $color = '#8F7AB5';
        if ($variation) {
            $color = '#DCC7FF';
        }

        if ($lighten) {
            $color = $this->adjustBrightness($color, $lighten);
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

    public function getSelectedExtraBenchmarks()
    {
        // Clear this out if multitrend is off
        $config = $this->getConfig();
        if (empty($config['multiTrend'])) {
            $this->selectedExtraBenchmarks = array();
        }

        return $this->selectedExtraBenchmarks;
    }
}
