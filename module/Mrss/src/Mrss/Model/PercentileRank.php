<?php

namespace Mrss\Model;

use \Mrss\Entity\PercentileRank as PercentileRankEntity;
use \Mrss\Entity\College as CollegeEntity;
use \Mrss\Entity\Benchmark as BenchmarkEntity;
use \Mrss\Entity\Study as StudyEntity;

/**
 * Class PercentileRank
 *
 * @package Mrss\Model
 */
class PercentileRank extends AbstractModel
{
    protected $entity = 'Mrss\Entity\PercentileRank';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function findOneByCollegeBenchmarkAndYear(
        $college,
        $benchmark,
        $year,
        $system = null
    ) {
        return $this->getRepository()->findOneBy(
            array(
                'college' => $college,
                'benchmark' => $benchmark,
                'year' => $year,
                'system' => $system
            )
        );
    }

    /**
     * @param CollegeEntity $college
     * @param \Mrss\Entity\Study $study
     * @param $year
     * @param bool $weaknesses
     * @param null $benchmarkGroupToExclude
     * @param int $limit
     * @return PercentileRankEntity[]
     */
    public function findStrengths(
        CollegeEntity $college,
        StudyEntity $study,
        $year,
        $weaknesses = false,
        $benchmarkGroupToExclude = null,
        $limit = 5
    ) {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        //$connection->setFetchMode('\Mrss\Entity\PercentileRank');
        $qb = $em->createQueryBuilder();

        //->setFetchMode('MyBundle\Entity\User', 'addresses', \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EAGER)


        // For some benchmarks, lower is better. Calculate an absolute rank so we can sort by that.
        $absoluteRank = 'CASE WHEN b.highIsBetter = true THEN p.rank ELSE 100 - p.rank END AS absolute_rank';

        $qb->select(array('p', $absoluteRank));
        $qb->from('\Mrss\Entity\Benchmark', 'b');

        // Join subscriptions
        $qb->innerJoin(
            '\Mrss\Entity\PercentileRank',
            'p',
            'WITH',
            'p.benchmark = b.id'
        );

        $qb->andWhere("b.includeInBestPerformer = TRUE");

        $qb->andWhere("p.college = :college_id");
        $qb->setParameter('college_id', $college->getId());

        $qb->andWhere("p.study = :study_id");
        $qb->setParameter('study_id', $study->getId());

        $qb->andWhere("p.year = :year");
        $qb->setParameter('year', $year);

        $qb->andWhere('p.system IS NULL');

        if (!$weaknesses) {
            $qb->orderBy('absolute_rank', 'DESC');
        } else {
            $qb->orderBy('absolute_rank', 'ASC');
        }

        if ($benchmarkGroupToExclude) {
            $qb->andWhere('b.benchmarkGroup != :group_id');
            $qb->setParameter('group_id', $benchmarkGroupToExclude);
        }

        $qb->setFirstResult(0)->setMaxResults($limit);


        try {
            //$results = $qb->getQuery()->getResult();
            $results = $qb->getQuery()->getResult();
        } catch (\Exception $e) {
            prd($e->getMessage());
            return array();
        }

        // Convert back to array of rank objects
        $ranks = array();
        foreach ($results as $row) {
            $rank = $row[0];
            $hib = ($row['absolute_rank'] == $rank->getRank());
            $rank->setHighIsBetter($hib);

            $ranks[] = $rank;
        }
        $results = $ranks;

        return $results;
    }

    public function findBestPerformers(StudyEntity $study, BenchmarkEntity $benchmark, $year, $threshold)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        // For some benchmarks, lower is better. Calculate an absolute rank so we can sort by that.
        $absoluteRank = 'CASE WHEN b.highIsBetter = true THEN p.rank ELSE 100 - p.rank END AS absolute_rank';

        $qb->select(array('c'/*, $absoluteRank*/));
        $qb->from('\Mrss\Entity\College', 'c');

        // Join subscriptions
        $qb->innerJoin(
            '\Mrss\Entity\PercentileRank',
            'p',
            'WITH',
            'p.college = c.id'
        );

        //$qb->andWhere("b.includeInBestPerformer = TRUE");

        $qb->andWhere("p.benchmark = :benchmark_id");
        $qb->setParameter('benchmark_id', $benchmark->getId());

        $qb->andWhere("p.study = :study_id");
        $qb->setParameter('study_id', $study->getId());

        $qb->andWhere("p.year = :year");
        $qb->setParameter('year', $year);

        $qb->andWhere('p.system IS NULL');

        if ($benchmark->getHighIsBetter()) {
            $qb->andWhere('p.rank > :threshold');
            $qb->setParameter('threshold', $threshold);
        } else {
            $qb->andWhere('p.rank < :threshold');
            $invertedThreshold = 100 - $threshold;
            $qb->setParameter('threshold', $invertedThreshold);
        }


        try {
            $results = $qb->getQuery()->getResult();
        } catch (\Exception $e) {
            prd($e->getMessage());
            return array();
        }

        /*// Convert back to array of rank objects
        $colleges = array();
        foreach ($results as $row) {
            $college = $row;
            pr($college->getName());
            $colleges[] = $college;
        }

        $results = $colleges;*/

        return $results;
    }

    public function save(PercentileRankEntity $PercentileRank)
    {
        $this->getEntityManager()->persist($PercentileRank);

        // Flush here or leave it to some other code?
    }

    public function deleteByStudyAndYear($studyId, $year, $system = null)
    {
        $dql = 'DELETE Mrss\Entity\PercentileRank p
            WHERE p.year = ?1
            AND p.study = ?2';

        if ($system) {
            $dql .= ' AND p.system = ?3';
        } else {
            $dql .= ' AND p.system IS NULL';
        }


        $query = $this->getEntityManager()->createQuery($dql);

        $query->setParameter(1, $year);
        $query->setParameter(2, $studyId);

        if ($system) {
            $query->setParameter(3, $system);
        }

        $query->execute();
    }
}
