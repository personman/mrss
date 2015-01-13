<?php

namespace Mrss\Model;

use \Mrss\Entity\Subscription as SubscriptionEntity;
use \Mrss\Entity\Study as StudyEntity;

/**
 * Class Subscription
 *
 * @package Mrss\Model
 */
class Subscription extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Subscription';

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
     * @return SubscriptionEntity[]
     */
    public function findByStudyAndYear($studyId, $year)
    {
        /*return $this->getRepository()->findBy(
            array(
                'study' => $studyId,
                'year' => $year
            )
        );*/

        $query = $this->getEntityManager()->createQuery(
            "SELECT s
            FROM Mrss\Entity\Subscription s
            INNER JOIN Mrss\Entity\College c
            WHERE s.college = c.id
            AND s.study = $studyId
            AND s.year = $year
            ORDER BY c.name ASC"
        );

        return $query->getResult();

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
