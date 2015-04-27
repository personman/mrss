<?php

namespace Mrss\Service\Report\Max\ActivityReport;

use Mrss\Service\Report\Max\ActivityReport;

class StudentServicesPercentages extends StudentServices
{
    protected function getTopLevelBenchmarkKey($activity)
    {
        return "ss_{$activity}_budget";
    }

    protected function getDetailColumns($activity)
    {
        return array();
    }
}
