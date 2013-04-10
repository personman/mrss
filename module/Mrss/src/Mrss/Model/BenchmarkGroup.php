<?php

namespace Mrss\Model;

use \Mrss\Entity\BenchmarkGroup as BenchmarkGroupEntity;
use Zend\Debug\Debug;

/**
 * Class BenchmarkGroup
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class BenchmarkGroup extends AbstractModel
{
    protected $entity = 'Mrss\Entity\BenchmarkGroup';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all benchmark groups, ordered by sequence
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('sequence' => 'ASC'));
        return $c;
    }

    /**
     * Save it with Doctrine
     *
     * @param BenchmarkGroupEntity $benchmarkGroup
     */
    public function save(BenchmarkGroupEntity $benchmarkGroup)
    {
        $this->getEntityManager()->persist($benchmarkGroup);

        // Flush here or leave it to some other code?
    }
}
