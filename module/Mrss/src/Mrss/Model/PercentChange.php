<?php

namespace Mrss\Model;

use Mrss\Entity\PercentChange as PercentChangeEntity;

/**
 * Class PercentChange
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Cms\Model
 */
class PercentChange extends AbstractModel
{
    protected $entity = 'Mrss\Entity\PercentChange';

    /**
     * @param $id
     * @return null|PercentChangeEntity
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param $college
     * @param $year
     * @return \Mrss\Entity\PercentChange[]
     */
    public function findByCollegeAndYear($college, $year)
    {
        return $this->getRepository()->findBy(
            array(
                'college' => $college,
                'year' => $year
            )
        );
    }

    /**
     * @param $benchmark
     * @param $year
     * @return \Mrss\Entity\PercentChange[]
     */
    public function findByBenchmarkAndYear($benchmark, $year)
    {
        return $this->getRepository()->findBy(
            array(
                'benchmark' => $benchmark,
                'year' => $year
            )
        );
    }

    /**
     * @param $year
     * @return PercentChangeEntity[]
     */
    public function findByYear($year)
    {
        return $this->getRepository()->findBy(
            array(
                'year' => $year
            )
        );
    }

    /**
     * Find all pages, ordered by title
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    public function save(PercentChangeEntity $change)
    {
        $this->getEntityManager()->persist($change);
    }

    public function delete(PercentChangeEntity $change)
    {
        $this->getEntityManager()->remove($change);
        $this->getEntityManager()->flush();
    }

    public function deleteByCollegeAndYear($college, $year)
    {
        foreach ($this->findByCollegeAndYear($college, $year) as $change) {
            $this->delete($change);
        }
    }
}
