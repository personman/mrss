<?php

namespace Mrss\Model;

use Mrss\Entity\BenchmarkHeading as BenchmarkHeadingEntity;

/**
 * Class BenchmarkHeading
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class BenchmarkHeading extends AbstractModel
{
    protected $entity = 'Mrss\Entity\BenchmarkHeading';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all benchmarks, ordered by sequence
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('sequence' => 'ASC'));
        return $c;
    }

    public function save(BenchmarkHeadingEntity $heading)
    {
        // Confirm that the sequence is set
        if ($heading->getSequence() === null) {
            $heading->setSequence(0);
        }

        $this->getEntityManager()->persist($heading);

        // Flush here or leave it to some other code?
    }

    public function delete(BenchmarkHeadingEntity $heading)
    {
        $this->getEntityManager()->remove($heading);
        $this->getEntityManager()->flush();

    }
}
