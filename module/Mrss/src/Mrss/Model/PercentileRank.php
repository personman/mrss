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

    public function find($identifier)
    {
        return $this->getRepository()->find($identifier);
    }

    /**
     * @param int|\Mrss\Entity\College $college
     * @param \Mrss\Entity\Benchmark $benchmark
     * @param $year
     * @param null|\Mrss\Entity\System $system
     * @return PercentileRankEntity
     */
    public function findOneByCollegeBenchmarkAndYear(
        $college,
        $benchmark,
        $year,
        $system = null,
        $forPercentChange = false
    ) {

        if (!is_int($college)) {
            $collegeId = $college->getId();
        } else {
            $collegeId = $college;
        }

        $query = $this->getBaseQuery();

        $query->select(array('r'));
        $query->from('\Mrss\Entity\PercentileRank', 'r');

        $query->andWhere('r.college = :college_id');
        $query->setParameter('college_id', $collegeId);

        $query->andWhere('r.benchmark = :benchmark_id');
        $query->setParameter('benchmark_id', $benchmark->getId());

        if ($forPercentChange) {
            $query->andWhere('r.forPercentChange = 1');
        } else {
            $query->andWhere('r.forPercentChange = 0');
        }


        //$query->setParameter('forPercentChange', $forPercentChange);

        $query->andWhere('r.year = :year');
        $query->setParameter('year', $year);

        if ($system) {
            $query->andWhere('r.system = :system_id');
            $query->setParameter('system_id', $system->getId());
        } else {
            $query->andWhere('r.system IS NULL');
        }

        $result = null;
        try {
            $results = $query->getQuery()->getResult();
            if (count($results)) {
                $result = $results[0];
            }
        } catch (\Exception $error) {
            prd($error->getMessage());
        }

        /*pr($collegeId);
        pr($benchmark->getId());
        prd($query->getDQL());*/

        return $result;
    }

    protected function getBaseQuery()
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQueryBuilder();

        return $query;
    }

    /**
     * @param CollegeEntity $college
     * @param \Mrss\Entity\Study $study
     * @param $year
     * @param bool $weaknesses
     * @param null $formToExclude
     * @param int $threshold
     * @param int $systemId
     * @return PercentileRankEntity[]
     */
    public function findStrengths(
        CollegeEntity $college,
        StudyEntity $study,
        $year,
        $weaknesses = false,
        $formToExclude = null,
        $threshold = 85,
        $systemId = null
    ) {
        $query = $this->getBaseQuery();

        // For some benchmarks, lower is better. Calculate an absolute rank so we can sort by that.
        $absoluteRank = 'CASE WHEN b.highIsBetter = true THEN p.rank ELSE 100 - p.rank END AS absolute_rank';

        $query->select(array('p', $absoluteRank));
        $query->from('\Mrss\Entity\Benchmark', 'b');

        // Join subscriptions
        $query->innerJoin(
            '\Mrss\Entity\PercentileRank',
            'p',
            'WITH',
            'p.benchmark = b.id'
        );

        $query->andWhere("b.includeInBestPerformer = TRUE");

        $query->andWhere("p.college = :college_id");
        $query->setParameter('college_id', $college->getId());

        $query->andWhere("p.study = :study_id");
        $query->setParameter('study_id', $study->getId());

        $query->andWhere("p.forPercentChange = 0");

        $query->andWhere("p.year = :year");
        $query->setParameter('year', $year);

        if (is_null($systemId)) {
            $query->andWhere('p.system IS NULL');
        } else {
            $query->andWhere('p.system = :system');
            $query->setParameter('system', $systemId);
        }


        // Only fetch results that surpass a threshold
        if (!empty($threshold)) {
            if (!$weaknesses) {
                $query->having('absolute_rank >= :threshold');
                $query->setParameter('threshold', $threshold);
            } else {
                $query->having('absolute_rank <= :threshold');
                $query->setParameter('threshold', 100 - $threshold);
            }
        }

        if (!$weaknesses) {
            $query->orderBy('absolute_rank', 'DESC');
        } else {
            $query->orderBy('absolute_rank', 'ASC');
        }

        if ($formToExclude) {
            $query->andWhere('b.benchmarkGroup != :group_id');
            $query->setParameter('group_id', $formToExclude);
        }


        try {
            //$results = $query->getQuery()->getResult();
            $results = $query->getQuery()->getResult();
        } catch (\Exception $error) {
            prd($error->getMessage());
            return array();
        }

        // Convert back to array of rank objects
        $ranks = array();
        foreach ($results as $row) {
            /** @var \Mrss\Entity\PercentileRank $rank */
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
        $query = $this->getBaseQuery();

        // For some benchmarks, lower is better. Calculate an absolute rank so we can sort by that.
        //$absoluteRank = 'CASE WHEN b.highIsBetter = true THEN p.rank ELSE 100 - p.rank END AS absolute_rank';

        $query->select(array('c'));
        $query->from('\Mrss\Entity\College', 'c');

        // Join subscriptions
        $query->innerJoin(
            '\Mrss\Entity\PercentileRank',
            'p',
            'WITH',
            'p.college = c.id'
        );

        //$query->andWhere("b.includeInBestPerformer = TRUE");

        $query->andWhere("p.benchmark = :benchmark_id");
        $query->setParameter('benchmark_id', $benchmark->getId());

        $query->andWhere("p.study = :study_id");
        $query->setParameter('study_id', $study->getId());

        $query->andWhere("p.forPercentChange = 0");

        $query->andWhere("p.year = :year");
        $query->setParameter('year', $year);

        $query->andWhere('p.system IS NULL');

        if ($benchmark->getHighIsBetter()) {
            $query->andWhere('p.rank > :threshold');
            $query->setParameter('threshold', $threshold);
        } else {
            $query->andWhere('p.rank < :threshold');
            $invertedThreshold = 100 - $threshold;
            $query->setParameter('threshold', $invertedThreshold);
        }

        try {
            $results = $query->getQuery()->getResult();
        } catch (\Exception $error) {
            prd($error->getMessage());
            return array();
        }

        return $results;
    }

    public function save(PercentileRankEntity $percentileRank)
    {
        $this->getEntityManager()->persist($percentileRank);

        // Flush here or leave it to some other code?
    }

    public function deleteByStudyAndYear($studyId, $year, $system = null, $forPercentChange = false)
    {
        $dql = 'DELETE Mrss\Entity\PercentileRank p
            WHERE p.year = ?1
            AND p.study = ?2
            AND p.forPercentChange = ?3';

        if ($system) {
            $dql .= ' AND p.system IS NOT NULL';
        } else {
            $dql .= ' AND p.system IS NULL';
        }


        $query = $this->getEntityManager()->createQuery($dql);

        $query->setParameter(1, $year);
        $query->setParameter(2, $studyId);
        $query->setParameter(3, $forPercentChange);


        if ($system) {
            //$query->setParameter(3, $system);
        }

        $query->execute();
    }
}
