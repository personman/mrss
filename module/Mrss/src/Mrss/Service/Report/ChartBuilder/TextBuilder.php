<?php

namespace Mrss\Service\Report\ChartBuilder;

use Mrss\Service\Report\ChartBuilder;

class TextBuilder extends ChartBuilder
{
    public function getChart()
    {
        $config = $this->getConfig();
        prd($config);
        // Just return the content
        return $config['content'];
    }
}
