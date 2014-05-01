<?php

namespace Mrss\Model;

use \Mrss\Entity\SubObservation as SubObservationEntity;

/**
 * Class SubObservation
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class SubObservation extends AbstractModel
{
    /**
     * The entity we're working with
     *
     * @var string
     */
    protected $entity = 'Mrss\Entity\SubObservation';

    /**
     * Find an observation by its id
     *
     * @param $id
     * @return null|object
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }


    /**
     * Save
     *
     * @param SubObservationEntity $observation
     */
    public function save(SubObservationEntity $observation)
    {
        $this->getEntityManager()->persist($observation);

        // Flush here or leave it to some other code?
    }

    /**
     * @param SubObservationEntity $subObservation
     */
    public function delete(SubObservationEntity $subObservation)
    {
        $this->getEntityManager()->remove($subObservation);
        $this->getEntityManager()->flush();
    }

}
