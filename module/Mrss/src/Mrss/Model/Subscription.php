<?php

namespace Mrss\Model;

use \Mrss\Entity\Subscription as SubscriptionEntity;

/**
 * Class Subscription
 *
 * @package Mrss\Model
 */
class Subscription extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Subscription';

    public function findOne($year, $collegeId, $studyId)
    {
        return $this->getRepository()->findOneBy(
            array(
                'year' => $year,
                'college' => $collegeId,
                'study' => $studyId
            )
        );
    }

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function save(SubscriptionEntity $subscription)
    {
        $this->getEntityManager()->persist($subscription);

        // Flush here or leave it to some other code?
    }
}
