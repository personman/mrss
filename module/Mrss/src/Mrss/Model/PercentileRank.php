<?php

namespace Mrss\Model;

use \Mrss\Entity\PercentileRank as PercentileRankEntity;
use \Mrss\Entity\College as CollegeEntity;
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
     * @return PercentileRankEntity[]
     */
    public function findStrengths(
        CollegeEntity $college,
        StudyEntity $study,
        $year,
        $weaknesses = false,
        $benchmarkGroupToExclude = null
    ) {

        $limit = 5;



        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        //$connection->setFetchMode('\Mrss\Entity\PercentileRank');
        $qb = $em->createQueryBuilder();

        //->setFetchMode('MyBundle\Entity\User', 'addresses', \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EAGER)



        $qb->select('p');
        $qb->from('\Mrss\Entity\Benchmark', 'b');

        // Join subscriptions
        $qb->innerJoin(
            '\Mrss\Entity\PercentileRank',
            'p',
            'WITH',
            'p.benchmark = b.id'
        );

        $qb->andWhere("b.includeInNationalReport = TRUE");

        $qb->andWhere("p.college = :college_id");
        $qb->setParameter('college_id', $college->getId());

        $qb->andWhere("p.study = :study_id");
        $qb->setParameter('study_id', $study->getId());

        $qb->andWhere("p.year = :year");
        $qb->setParameter('year', $year);

        $qb->andWhere('p.system IS NULL');

        if (!$weaknesses) {
            $qb->orderBy('p.rank', 'DESC');
        } else {
            $qb->orderBy('p.rank', 'ASC');
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

        /*
        $connection = $this->getEntityManager()->getConnection();
        $connection->setFetchMode(\PDO::FETCH_ASSOC);

        $qb = $connection->createQueryBuilder();

        $qb->select('p');
        //$qb->from('\Mrss\Entity\PercentileRank', 'p');
        $qb->from('\Mrss\Entity\Benchmark', 'b');

        // Join benchmarks so we only get report ones
        $qb->innerJoin(
            '\Mrss\Entity\PercentileRank',
            'p',
            'WITH',
            'b.id = p.benchmark'
        );

        $qb->where("b.includeInNationalReport IS TRUE");
        $qb->where("p.college_id =  :college_id");
        $qb->setParameter('college_id', $college->getId());

        $qb->where("p.year =  :year");
        $qb->setParameter('year', $year);

        if (!$weaknesses) {
            $qb->orderBy('p.rank', 'DESC');
        } else {
            $qb->orderBy('p.rank', 'ASC');
        }

        $qb->setFirstResult(0)->setMaxResults($limit);


        try {
            $results = $qb->execute()->fetchAll();
        } catch (\Exception $e) {
            prd($e->getMessage());
            return array();
        }
        //prd($results);
        */
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
