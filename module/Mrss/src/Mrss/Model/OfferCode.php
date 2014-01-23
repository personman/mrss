<?php

namespace Mrss\Model;

use \Mrss\Entity\OfferCode as OfferCodeEntity;
use Zend\Debug\Debug;

/**
 * Class OfferCode
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class OfferCode extends AbstractModel
{
    protected $entity = 'Mrss\Entity\OfferCode';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all systems, ordered by name
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('name' => 'ASC'));
        return $c;
    }


    public function save(OfferCodeEntity $offerCode)
    {
        $this->getEntityManager()->persist($offerCode);
    }

    public function delete(OfferCodeEntity $offerCode)
    {
        $this->getEntityManager()->remove($offerCode);
        $this->getEntityManager()->flush();
    }
}
