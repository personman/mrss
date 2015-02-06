<?php

namespace Mrss\Model;

use \Mrss\Entity\PeerBenchmark as PeerBenchmarkEntity;
use Zend\Debug\Debug;

/**
 * Class PeerBenchmark
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class PeerBenchmark extends AbstractModel
{
    protected $entity = 'Mrss\Entity\PeerBenchmark';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all systems, ordered by name
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array());
        return $c;
    }


    public function save(PeerBenchmarkEntity $peerBenchmark)
    {
        $this->getEntityManager()->persist($peerBenchmark);
    }

    public function delete(PeerBenchmarkEntity $peerBenchmark)
    {
        $this->getEntityManager()->remove($peerBenchmark);
        $this->getEntityManager()->flush();
    }
}
