<?php

namespace Mrss\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Mrss\Entity\User;
use Mrss\Controller\Plugin\SystemActiveCollege;
use Mrss\Controller\Plugin\CurrentStudy as CurrentStudyPlugin;
use Zend\Form\Form;

/**
 * Display a chart
 */
class Chart extends AbstractHelper
{
    /**
     * @var string
     */
    protected $chartJsUri = '/js/highcharts.js';

    protected $exportingJsUri = '/js/highcharts-exporting.js';

    protected $exportingCsvJsUri = '/js/highcharts-exporting-csv.js';

    protected $moreJsUri = '/js/highcharts-more.js';

    protected $chartSupportJsUri = '/js/chart-support.js';

    protected $javascriptPlaced = false;

    public function __invoke($chartConfig = null)
    {
        if ($chartConfig === null) {
            return $this;
        }

        return $this->showChart($chartConfig);
    }

    public function showChart($chartConfig)
    {
        $this->headScript();
        $chartConfigJson = $this->encodeAndAddEvents($chartConfig);


        $html = '<div class="chartWrapper">';

        $chartId = 'chart_' . $chartConfig['id'];
        $html .= "<div id='$chartId' class='chart'></div>";

        $html .= "<script type='text/javascript'>
        $(function() {
            var chartConfig = $chartConfigJson;
            //chartConfig = addFormatters(chartConfig)
            $('#$chartId').highcharts(chartConfig)
        })
    </script>";

        if (!empty($chartConfig['description'])) {
            $html .= "<p>" . $chartConfig['description'] . "</p>";
        }

        $html .= '</div>';

        return $html;
    }

    public function encodeAndAddEvents($chartConfig)
    {
        $events = array();
        if (!empty($chartConfig['chart']['events'])) {
            $events = $chartConfig['chart']['events'];
        }

        $config = json_encode($chartConfig);

        foreach ($events as $event => $function) {
            $functionLabel = '"' . $function .  '"';
            $script = "function (event) { $function(event, this) }";
            $config = str_replace($functionLabel, $script, $config);
        }

        return $config;
    }

    public function headScript()
    {
        if (!$this->javascriptPlaced) {
            $this->getView()->headScript()->appendFile(
                $this->getChartJsUri(),
                'text/javascript'
            );

            $this->getView()->headScript()->appendFile(
                $this->moreJsUri,
                'text/javascript'
            );

            $this->getView()->headScript()->appendFile(
                $this->exportingJsUri,
                'text/javascript'
            );

            $this->getView()->headScript()->appendFile(
                $this->exportingCsvJsUri,
                'text/javascript'
            );

            /* Deprecated
            $this->getView()->headScript()->appendFile(
                $this->chartSupportJsUri,
                'text/javascript'
            );*/

            /*$this->getView()->headScript()->appendFile(
                '/js/highcharts-regression.js?v=1',
                'text/javascript'
            );*/

            $this->getView()->headScript()->appendScript($this->getSetupOptions());


            $this->javascriptPlaced = true;
        }
    }

    public function getChartJsUri()
    {
        return $this->chartJsUri;
    }

    /**
     * Set thousands separator
     * 
     * @return string
     */
    protected function getSetupOptions()
    {
        return "$(function() {
            Highcharts.setOptions({lang: {thousandsSep: ','}})
        })";
    }
}
