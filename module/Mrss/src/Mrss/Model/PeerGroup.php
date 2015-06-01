<?php

namespace Mrss\Model;

use \Mrss\Entity\PeerGroup as PeerGroupEntity;
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
class PeerGroup extends AbstractModel
{
    protected $entity = 'Mrss\Entity\PeerGroup';

    /**
     * @param $id
     * @return null|\Mrss\Entity\PeerGroup
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function findOneByCollegeAndName($college, $name)
    {
        return $this->getRepository()->findOneBy(
            array(
                'college' => $college,
                'name' => $name
            )
        );
    }

    /**
     * Find all systems, ordered by name
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('name' => 'ASC'));
        return $c;
    }


    public function save(PeerGroupEntity $peerGroup)
    {
        $this->getEntityManager()->persist($peerGroup);
    }

    public function delete(PeerGroupEntity $peerGroup)
    {
        $this->getEntityManager()->remove($peerGroup);
        $this->getEntityManager()->flush();
    }
}
