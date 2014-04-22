<?php

namespace Mrss\Service;

use Mrss\Entity\Observation;

class ObservationAudit
{
    public function compare(Observation $old, Observation $new)
    {
        $changes = array();

        $benchmarks = $old->getAllBenchmarks();
        foreach ($benchmarks as $benchmark) {
            $oldValue = $old->get($benchmark);
            $newValue = $new->get($benchmark);

            if ($oldValue != $newValue) {
                $changes[$benchmark] = array(
                    'old' => $oldValue,
                    'new' => $newValue
                );
            }
        }

        return $changes;
    }
}
