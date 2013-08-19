<?php

namespace Mrss\Model;

use \Mrss\Entity\Payment as PaymentEntity;

/**
 * Class Payment
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Payment extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Payment';

    public function findByTransId($transId)
    {
        return $this->getRepository()->findOneBy(array('transId' => $transId));
    }

    /**
     * Save it with Doctrine
     *
     * @param PaymentEntity $study
     */
    public function save(PaymentEntity $study)
    {
        $this->getEntityManager()->persist($study);

        $this->getEntityManager()->flush();
    }
}
