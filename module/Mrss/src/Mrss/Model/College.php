<?php

namespace Mrss\Model;

use \Mrss\Entity\College as CollegeEntity;
use \Mrss\Entity\PeerGroup;
use Zend\Debug\Debug;

/**
 * Class College
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class College extends AbstractModel
{
    protected $entity = 'Mrss\Entity\College';

    public function findOneByIpeds($ipeds)
    {
        return $this->getRepository()->findOneBy(array('ipeds' => $ipeds));
    }

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all colleges, ordered by name
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('name' => 'ASC'));
        return $c;
    }

    public function findByPeerGroup(PeerGroup $peerGroup)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->add('select', 'c');
        $qb->add('from', '\Mrss\Entity\College c');

        // Join subscriptions
        $qb->innerJoin(
            '\Mrss\Entity\Subscription',
            's',
            'WITH',
            's.college = c.id'
        );
        $qb->where('s.year = :year');
        $qb->setParameter('year', $peerGroup->getYear());

        // Filter by state
        $states = $peerGroup->getStates();
        if (is_array($states) && count($states) > 0) {
            $qb->andWhere($qb->expr()->in('c.state', ':states'));
            $qb->setParameter('states', $states);
        }

        // Join observations
        $qb->innerJoin(
            '\Mrss\Entity\Observation',
            'o',
            'WITH',
            's.observation = o.id'
        );

        // Filter by campus environment
        if ($peerGroup->getEnvironments()) {
            $qb->andWhere(
                $qb->expr()->in(
                    'o.institutional_demographics_campus_environment',
                    ':environments'
                )
            );
            $qb->setParameter(
                'environments',
                $peerGroup->getEnvironments()
            );
        }

        // Filter by workforce enrollment
        if ($peerGroup->getWorkforceEnrollment()) {
            $qb->andWhere(
                'o.enrollment_information_unduplicated_enrollment BETWEEN
                :enrollment_min AND :enrollment_max'
            );
            $qb->setParameter(
                'enrollment_min',
                $peerGroup->getWorkforceEnrollment('min')
            );
            $qb->setParameter(
                'enrollment_max',
                $peerGroup->getWorkforceEnrollment('max')
            );
        }

        // Filter by workforce revenue
        if ($peerGroup->getWorkforceRevenue()) {
            $qb->andWhere(
                'o.revenue_total BETWEEN :revenue_min AND :revenue_max'
            );
            $qb->setParameter(
                'revenue_min',
                $peerGroup->getWorkforceRevenue('min')
            );
            $qb->setParameter(
                'revenue_max',
                $peerGroup->getWorkforceRevenue('max')
            );
        }

        // Filter by service area population
        if ($peerGroup->getServiceAreaPopulation()) {
            $qb->andWhere(
                'o.institutional_demographics_total_population BETWEEN
                :pop_min AND :pop_max'
            );
            $qb->setParameter(
                'pop_min',
                $peerGroup->getServiceAreaPopulation('min')
            );
            $qb->setParameter(
                'pop_max',
                $peerGroup->getServiceAreaPopulation('max')
            );
        }

        // Filter by service area unemployment
        if ($peerGroup->getServiceAreaUnemployment()) {
            $qb->andWhere(
                'o.institutional_demographics_unemployment_rate BETWEEN
                :unemployment_min AND :unemployment_max'
            );
            $qb->setParameter(
                'unemployment_min',
                $peerGroup->getServiceAreaUnemployment('min')
            );
            $qb->setParameter(
                'unemployment_max',
                $peerGroup->getServiceAreaUnemployment('max')
            );
        }

        // Filter by service area median income
        if ($peerGroup->getServiceAreaMedianIncome()) {
            $qb->andWhere(
                'o.institutional_demographics_median_household_income
                BETWEEN :income_min AND :income_max'
            );
            $qb->setParameter(
                'income_min',
                $peerGroup->getServiceAreaMedianIncome('min')
            );
            $qb->setParameter(
                'income_max',
                $peerGroup->getServiceAreaMedianIncome('max')
            );
        }

        // Order
        $qb->orderBy('c.name', 'ASC');


        if (false) {
            $dql = $qb->getDQL();
            var_dump($dql);
            $p = $qb->getParameters();
            var_dump($p);

            $colleges = $qb->getQuery()->getResult();
            $count = count($colleges);

            var_dump($count);
            var_dump($colleges);
            die;
            //var_dump($qb); die;*/
        }

        $colleges = $qb->getQuery()->getResult();

        return $colleges;
    }

    public function save(CollegeEntity $college)
    {
        $this->getEntityManager()->persist($college);

        // Flush here or leave it to some other code?
    }
}
