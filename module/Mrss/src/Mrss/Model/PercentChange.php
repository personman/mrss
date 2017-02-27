<?php

namespace Mrss\Model;

use Mrss\Entity\PercentChange as PercentChangeEntity;
use Mrss\Model\AbstractModel;

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
     * @param array $include
     * @param array $exclude
     * @param bool|true $includeNull
     * @return PercentChangeEntity[]
     */
    public function findByStatus($include = array(), $exclude = array(), $includeNull = true)
    {
        $where = '';
        if ($include) {
            $where = 'WHERE status IN (:include) ';
        }

        if ($includeNull) {
            $includeNull = ' OR i.status IS NULL';
        }

        if ($exclude) {
            if ($where) {
                $where .= ' AND ';
            } else {
                $where .= 'WHERE ';
            }

            $where .= "(i.status NOT IN (:exclude) $includeNull)";
        }

        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT i
            FROM Mrss\Entity\PercentChange c
            $where
            ORDER BY c.status DESC"
        );

        if ($include) {
            $query->setParameter('include', $include);
        }
        if ($exclude) {
            $query->setParameter('exclude', $exclude);
        }

        $results = $query->getResult();

        /*
        try {
            $results = $query->getResult();

        } catch (\Exception $e) {
            return array();
        }*/

        return $results;
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
