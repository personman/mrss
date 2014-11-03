<?php

namespace Mrss\Model;

use \Mrss\Entity\Observation as ObservationEntity;
use Mrss\Entity\Benchmark as BenchmarkEntity;
use Mrss\Entity\College as CollegeEntity;
use Zend\Debug\Debug;

/**
 * Class Observation
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Observation extends AbstractModel
{
    /**
     * The entity we're working with
     *
     * @var string
     */
    protected $entity = 'Mrss\Entity\Observation';

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
     * Find an observation by college, year, cipcode
     *
     * @param $collegeId
     * @param $year
     * @param int $cipCode
     * @return null|object
     */
    public function findOne($collegeId, $year, $cipCode = 0)
    {
        $criteria = array(
            'college' => $collegeId,
            'year' => $year,
        );

        if ($cipCode) {
            $criteria['cipCode'] = $cipCode;
        }

        return $this->getRepository()->findOneBy($criteria);
    }

    public function findByYear($year)
    {
        return $this->getRepository()->findAll(
            array(
                'year' => $year
            )
        );
    }

    /**
     * Return all observations belonging to a subscription for the given year
     * and study.
     *
     * @param $year
     * @param $study
     * @return array
     */
    public function findByYearAndStudy($year, $study)
    {
        // Prepare a queryBuilder
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->add('select', 'o');
        $qb->add('from', '\Mrss\Entity\Observation o');


        // Join subscriptions
        $qb->innerJoin(
            '\Mrss\Entity\Subscription',
            's',
            'WITH',
            's.observation = o.id'
        );

        $qb->andWhere('s.year = :year');
        $qb->setParameter('year', $year);

        $qb->andWhere('s.study = :study_id');
        $qb->setParameter('study_id', $study->getId());

        try {
            $data = $qb->getQuery()->getResult();
        } catch (\Exception $e) {
            return array();
        }

        return $data;
    }

    /**
     * Get all the observations
     *
     * @return array
     */
    public function findAll()
    {
        return $this->getRepository()->findAll(
            array(),
            array(
                'year' => 'ASC',
                'college_id' => 'ASC'
            )
        );
    }

    /**
     * Return data for a single benchmark, multiple colleges, multiple years
     */
    public function findForChart($benchmarkColumn, $collegeIds)
    {
        // Headers based on colleges
        $selects = array('year');
        $headers = array('Year');
        foreach ($collegeIds as $collegeId) {
            $selects[] = "MAX( IF( college_id = $collegeId, $benchmarkColumn,
            NULL)) AS c$collegeId";
            $headers[] = "c" . $collegeId;
        }

        // Prepare a queryBuilder
        $connection = $this->getEntityManager()->getConnection();
        $connection->setFetchMode(\PDO::FETCH_NUM);
        $qb = $connection->createQueryBuilder();

        // The query
        $qb->select($selects);
        $qb->from('observations', 'o');
        $qb->groupBy('year');

        $data = $qb->execute()->fetchAll();

        //return array_merge(array($headers), $data);
        return $data;
    }

    /**
     * Sparklines, return an array of values over years for a benchmark and college
     *
     * @param \Mrss\Entity\Benchmark|\Mrss\Model\Benchmark $benchmark
     * @param \Mrss\Entity\College|\Mrss\Model\College $college
     * @return array
     */
    public function getSparkline(BenchmarkEntity $benchmark, CollegeEntity $college)
    {
        // Headers based on colleges
        $selects = array('year', $benchmark->getDbColumn());

        // Prepare a queryBuilder
        $connection = $this->getEntityManager()->getConnection();
        $connection->setFetchMode(\PDO::FETCH_NUM);
        $qb = $connection->createQueryBuilder();

        // The query
        $qb->select($selects);
        $qb->from('observations', 'o');
        $qb->where('college_id = :college');
        $qb->setParameter('college', $college->getId());
        $qb->orderBy('year');

        try {
            $data = $qb->execute()->fetchAll();
        } catch (\Exception $e) {
            return array();
        }

        $sparkline = array();
        foreach ($data as $row) {
            if (!is_null($row[1])) {
                $sparkline[] = $row[1];
            }
        }

        return $sparkline;
    }

    /**
     * Save an observation
     *
     * @param ObservationEntity $observation
     */
    public function save(ObservationEntity $observation)
    {
        $this->getEntityManager()->persist($observation);

        // Flush here or leave it to some other code?
    }

    public function delete(ObservationEntity $observation)
    {
        $this->getEntityManager()->remove($observation);
    }
}
