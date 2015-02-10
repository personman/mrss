<?php

namespace Mrss\Service\Report\Max;

use Mrss\Service\Report;
use Mrss\Entity\Observation;

class Internal extends Report
{
    public function getInstructionalCosts(Observation $observation)
    {
        $data = array();

        // The institution-wide value
        $dbColumn = 'inst_total_expend_per_fte_student';
        $benchmark = $this->getBenchmark($dbColumn);
        $value = $benchmark->format($observation->get($dbColumn));
        $data[] = array(
            'label' => 'Total Costs Per FTE Student',
            'value' => $value
        );

        // Now the subobservations (academic units)
        foreach ($observation->getSubObservations() as $subObservation) {
            $label = $subObservation->getName() . ' Total Cost Per FTE Student';
            $value = '';
        }

        return $data;
    }
}
