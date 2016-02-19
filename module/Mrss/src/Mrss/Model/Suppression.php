<?php

namespace Mrss\Model;

use Mrss\Entity\Suppression as SuppressionEntity;

class Suppression extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Percentile';

    public function findBySubscription($subscription)
    {
        return $this->getRepository()->findBy(
            array(
                'subscription' => $subscription,
            )
        );
    }

    public function save(SuppressionEntity $entity)
    {
        $this->getEntityManager()->persist($entity);
    }

    public function delete(SuppressionEntity $suppression)
    {
        $this->getEntityManager()->remove($suppression);
    }
}
