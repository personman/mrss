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
    protected $chartJsUri = 'http://code.highcharts.com/highcharts.js';

    protected $exportingJsUri = 'http://code.highcharts.com/modules/exporting.js';

    protected $moreJsUri = 'http://code.highcharts.com/highcharts-more.js';

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
        $chartConfigJson = json_encode($chartConfig);

        $html = '<div class="chartWrapper">';

        $chartId = 'chart_' . $chartConfig['id'];
        $html .= "<div id='$chartId' class='chart'></div>";

        $html .= "<script type='text/javascript'>
        $(function() {
            $('#$chartId').highcharts($chartConfigJson)
        })
    </script>";

        if (!empty($chartConfig['description'])) {
            $html .= "<p>" . $chartConfig['description'] . "</p>";
        }

        $html .= '</div>';

        return $html;
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

            $this->javascriptPlaced = true;
        }
    }

    public function getChartJsUri()
    {
        return $this->chartJsUri;
    }
}
