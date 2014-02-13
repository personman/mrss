<?php

namespace Mrss\Service\NhebiSubscriptions;

/**
 * Class Nccbp
 *
 * For determining if a given ipeds/year combination returns a valid subscription
 *
 */
class Nccbp
{

    public function checkSubscription($year, $ipeds)
    {
        $query = "SELECT payment_id
          FROM nccbp_payment_institution
          WHERE subscribe_year = '%s'
          AND ipeds_id = '%s'";

        $results = db_query($query, $year, $ipeds);

        $subscriptionExists = false;
        while ($row = db_fetch_array($results)) {
            $subscriptionExists = true;
        }

        return $subscriptionExists;
    }
}
