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
    protected $chartJsUri = '/js/highcharts.js?v=5';

    protected $exportingJsUri = '/js/highcharts-exporting.js?v=4';

    protected $exportingCsvJsUri = '/js/highcharts-exporting-csv.js?v=3';

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
        if (!is_array($chartConfig)) {
            return false;
        }

        $class = 'chart';
        if (!empty($chartConfig['chart']['height']) && $height = $chartConfig['chart']['height']) {
            if ($height > 280) {
                $class .= ' tall';
            }
        }


        $this->headScript();
        $chartConfigJson = $this->encodeAndAddEvents($chartConfig);



        $html = '<div class="chartWrapper">';

        $chartId = 'chart_' . $chartConfig['id'];
        $html .= "<div id='$chartId' class='$class'></div>";

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

        // Legend format
        $multiTrend = 'false';
        if (!empty($chartConfig['multiTrend'])) {
            $multiTrend = 'true';
        }

        $formatter = "function () {return legendLabelFormatter(this, $multiTrend)}";
        $config = str_replace('"legendLabelFormatter"', $formatter, $config);

        $minuteSecondFormatter = 'ormatter":function () {return minuteSecondFormatter(this)}';
        $config = str_replace('ormat":"minuteSecondFormatter"', $minuteSecondFormatter, $config);

        $largeMoneyFormatter  = 'ormatter":function () {return formatLargeMoney(this)}';
        $config = str_replace('ormatter":"formatLargeMoney"', $largeMoneyFormatter, $config);

        $axisLabelFormatter = 'ormatter":function () {var formatter = this.axis.defaultLabelFormatter; return axisLabelFormatter(this, formatter, this.axis)}';
        $config = str_replace('ormatter":"axisLabelFormatter"', $axisLabelFormatter, $config);


        $regex = '/ormat":"{numericalOptions: {(.*?)}"/';
        preg_match($regex, $config, $matches);

        if (!empty($matches[1])) {
            $numOptions = '"{' . $matches[1] . '"';
            $numericalFormatter = 'ormatter": function () {return numericRadio(this, ' . $numOptions . ')}';
            //pr($config);
            $config = preg_replace($regex, $numericalFormatter, $config);
            //pr($config);
            //pr($numericalFormatter);
            //echo $config; die;
            //prd($matches);

        }
        //$config = preg_replace('ormat"\:"{numericalOptions\:{(.*)}', $numericalFormatter, $config);

        //pr($config);

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

            $this->getView()->headScript()->appendFile(
                '/js/highcharts-regression.js?v=3',
                'text/javascript'
            );

            $this->getView()->headScript()->appendFile(
                '/js/highcharts-solid-gauge.js?v=3',
                'text/javascript'
            );

            // Our plugin for legend subheadings
            $this->getView()->headScript()->appendFile(
                '/js/highcharts-legend-subheadings.js?v=1',
                'text/javascript'
            );

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
