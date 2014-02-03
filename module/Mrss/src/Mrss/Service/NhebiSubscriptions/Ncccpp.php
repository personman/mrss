<?php

namespace Mrss\Service\NhebiSubscriptions;

/**
 * Class Ncccpp
 *
 * For determining if a given ipeds/year combination returns a valid subscription
 *
 */
class Ncccpp
{
    protected $db;

    public function setDb($db)
    {
        $this->db = $db;

        return $this;
    }

    public function getDb()
    {
        return $this->db;
    }

    public function checkSubscription($year, $ipeds)
    {
        $subscriptionExists = false;

        $query = "SELECT payment_id, ipeds_unit_id
          FROM ncccpp_payment p
          INNER JOIN ncccpp_institution i ON i.institution_id = p.institution_id
          WHERE payment_year = :year
          AND ipeds_unit_id = :ipeds";

        $q = $this->getDb()->prepare($query);
        $q->execute(array('year' =>$year, 'ipeds' => $ipeds));
        $results = $q->fetchAll();

        if (!empty($results)) {
            $subscriptionExists = true;
        }

        return $subscriptionExists;
    }
}
