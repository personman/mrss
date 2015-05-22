<?php

namespace Mrss\Service\Report\Chart;

class ScatterPlot extends Bubble
{
    public function __construct()
    {
        parent::__construct();

        $config = $this->getConfig();

        $config['chart']['type'] = 'scatter';

        $this->setConfig($config);
    }
}
