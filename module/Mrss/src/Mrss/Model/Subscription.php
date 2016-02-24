<?php

namespace Mrss\Model;

use \Mrss\Entity\Subscription as SubscriptionEntity;
use \Mrss\Entity\Study as StudyEntity;
use \Mrss\Entity\College as CollegeEntity;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query;

/**
 * Class Subscription
 *
 * @package Mrss\Model
 */
class Subscription extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Subscription';

    /**
     * @param $year
     * @param $collegeId
     * @param $studyId
     * @return null|\Mrss\Entity\Subscription
     */
    public function findOne($year, $collegeId, $studyId)
    {
        return $this->getRepository()->findOneBy(
            array(
                'year' => $year,
                'college' => $collegeId,
                'study' => $studyId
            )
        );
    }

    /**
     * @param $collegeId
     * @param $studyId
     * @return Subscription[]
     */
    public function findByCollegeAndStudy($collegeId, $studyId)
    {
        return $this->getRepository()->findBy(
            array(
                'college' => $collegeId,
                'study' => $studyId
            )
        );
    }

    /**
     * @param $id
     * @return null|\Mrss\Entity\Subscription
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param $studyId
     * @param $year
     * @param bool $eagerObservation
     * @return SubscriptionEntity[]
     */
    public function findByStudyAndYear($studyId, $year, $eagerObservation = false, $order = 'c.name ASC', $limit = null)
    {
        $entities = 's';
        $joinOb = null;
        if ($eagerObservation) {
            $joinOb = "JOIN s.observation o";
            $entities = 's, o';
        }

        $dql = "SELECT $entities
            FROM Mrss\Entity\Subscription s
            JOIN s.college c
            $joinOb
            WHERE s.study = $studyId
            AND s.year = $year
            ORDER BY $order";

        $query = $this->getEntityManager()->createQuery($dql);

        if ($limit) {
            $query->setMaxResults($limit);
        }

        return $query->getResult();
    }

    /**
     * @param $studyId
     * @param $year
     * @return int
     */
    public function countByStudyAndYear($studyId, $year)
    {
        $dql = "SELECT COUNT(s)
            FROM Mrss\Entity\Subscription s
            WHERE s.study = $studyId
            AND s.year = $year";

        $query = $this->getEntityManager()->createQuery($dql);

        return $query->getSingleScalarResult();
    }

    /**
     * Return a list of subscriptions with an eagerly fetched partial observation.
     *
     * This is useful when we want to compare the value for one benchmark across all subscriptions for a
     * year and we don't want to load every single value for every observation. The $benchmarks array
     * specifies a subset of benchmarks to fetch. We can also optionally exclude outliers.
     *
     * @param $study
     * @param $year
     * @param array $benchmarks
     * @param boolean $excludeOutliers
     * @return SubscriptionEntity[]
     */
    public function findWithPartialObservations(
        $study,
        $year,
        $benchmarks,
        $excludeOutliers = true,
        $notNull = true,
        $benchmarkGroupIds = array()
    ) {
        $rsm = new ResultSetMapping;

        $rsm->addEntityResult('Mrss\Entity\Subscription', 's');
        $rsm->addFieldResult('s', 'id', 'id');
        $rsm->addFieldResult('s', 'paymentAmount', 'paymentAmount');

        $rsm->addJoinedEntityResult('Mrss\Entity\College', 'c', 's', 'college');
        $rsm->addFieldResult('c', 'college_id', 'id');
        $rsm->addFieldResult('c', 'name', 'name');
        $rsm->addFieldResult('c', 'state', 'state');

        $rsm->addJoinedEntityResult('Mrss\Entity\Observation', 'o', 's', 'observation');
        $rsm->addFieldResult('o', 'o_id', 'id');

        $subQueries = array();
        $notNulls = array();

        foreach ($benchmarks as $benchmark) {
            $rsm->addFieldResult('o', $benchmark, $benchmark);

            if ($excludeOutliers) {
                $subQueries[] = $this->getOutlierExclusionSubquery($benchmark);
            }

            if ($notNull) {
                $notNulls[] = " AND $benchmark IS NOT NULL ";
            }
        }
        $notNulls = implode(' ', $notNulls);

        // Suppression subquery
        $subQueries[] = $this->getSuppressionSubquery($benchmarkGroupIds);

        $benchmarkList = implode(', ', $benchmarks);
        $subQueries = implode("\n", $subQueries);
        if ($benchmarkList) {
            $benchmarkList = ', ' . $benchmarkList;
        }

        $sql = "SELECT s.id, c.id college_id, c.name, o.id o_id $benchmarkList
        FROM subscriptions s
        INNER JOIN colleges c ON s.college_id = c.id
        INNER JOIN observations o ON s.observation_id = o.id
        WHERE s.year = :year
        AND s.study_id = :study_id
        $notNulls
        $subQueries
        ";

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->setParameter('year', $year);
        $query->setParameter('study_id', $study->getId());

        // Force refresh so it doesn't serve stale entities (when multiple charts are built on one page)
        $query->setHint(Query::HINT_REFRESH, true);

        $result = $query->getResult();

        return $result;
    }

    protected function getOutlierExclusionSubquery($dbColumn)
    {
        return "AND NOT EXISTS (
            SELECT l.id
            FROM outliers l
            INNER JOIN benchmarks b2 ON l.benchmark_id = b2.id
            WHERE year = :year
            AND b2.dbColumn = '$dbColumn'
            AND l.college_id = c.id
        )";
    }

    protected function getSuppressionSubquery($benchmarkGroupIds)
    {
        if (count($benchmarkGroupIds)) {
            $ids = implode(', ', $benchmarkGroupIds);

            return " AND NOT EXISTS (
                SELECT sp.id
                FROM suppressions sp
                WHERE sp.subscription_id = s.id
                AND benchmarkGroup_id IN ($ids)
            )";
        }
    }

    /**
     * Look up the subscription record for the current study and year
     *
     * @param StudyEntity $study
     * @param $collegeId
     * @return array
     */
    public function findCurrentSubscription(StudyEntity $study, $collegeId)
    {
        return $this->findOne(
            $study->getCurrentYear(),
            $collegeId,
            $study->getId()
        );
    }

    /**
     * @param StudyEntity $study
     * @return array
     */
    public function getYearsWithSubscriptions(StudyEntity $study)
    {
        // Prepare a queryBuilder
        $connection = $this->getEntityManager()->getConnection();
        $qb = $connection->createQueryBuilder();

        // The query
        $qb->select('DISTINCT(year)');
        $qb->from('subscriptions', 's');
        $qb->andWhere('study_id = :study_id');
        $qb->setParameter('study_id', $study->getId());
        $qb->orderBy('year', 'DESC');

        $data = $qb->execute()->fetchAll();

        $years = array();
        foreach ($data as $row) {
            $years[] = $row['year'];
        }

        return $years;
    }

    public function getYearsWithReports(StudyEntity $study, CollegeEntity $college)
    {
        $subs = $this->findByCollegeAndStudy($college->getId(), $study->getId());

        // Skip the current year if reports aren't open yet
        $years = array();
        foreach ($subs as $key => $subscription) {
            $year = $subscription->getYear();
            if ($year == $study->getCurrentYear() && !$study->getReportsOpen()) {
                //unset($years[$key]);
            } else {
                $years[] = $year;
            }
        }

        return $years;
    }

    public function getLatestSubscription(StudyEntity $study, $collegeId, $before = null)
    {
        $studyId = $study->getId();

        $filters = array(
            'college' => $collegeId,
            'study' => $studyId
        );

        if (!is_null($before)) {
            $filters['year'] = range(2002, $before - 1);
        }

        return $this->getRepository()->findOneBy(
            $filters,
            array(
                'year' => 'DESC'
            )
        );
    }

    public function save(SubscriptionEntity $subscription)
    {
        $this->getEntityManager()->persist($subscription);

        // Flush here or leave it to some other code?
    }

    public function delete(SubscriptionEntity $subscription)
    {
        $this->getEntityManager()->remove($subscription);
    }
}
