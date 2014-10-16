<?php

namespace Mrss\Model;

use \Mrss\Entity\College as CollegeEntity;
use \Mrss\Entity\PeerGroup as PeerGroupEntity;
use \Mrss\Entity\Study as StudyEntity;
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

    /**
     * @param $ipeds
     * @return \Mrss\Entity\College|null
     */
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
     * @return \Mrss\Entity\College[]
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('name' => 'ASC'));
        return $c;
    }

    public function findByStudyAndYear(StudyEntity $study, $year)
    {
        $studyId = $study->getId();

        $query = $this->getEntityManager()->createQuery(
            "SELECT c
            FROM Mrss\Entity\College c
            INNER JOIN Mrss\Entity\Subscription s
            WHERE s.college = c.id
            AND s.study = $studyId
            AND s.year = $year
            ORDER BY c.name ASC"
        );

        return $query->getResult();
    }

    /**
     * Find all colleges that have ever subscribed to the study
     *
     * @param StudyEntity $study
     * @return CollegeEntity[]
     */
    public function findByStudy(StudyEntity $study)
    {
        $studyId = $study->getId();

        $query = $this->getEntityManager()->createQuery(
            "SELECT DISTINCT c
            FROM Mrss\Entity\College c
            INNER JOIN Mrss\Entity\Subscription s
            WHERE s.college = c.id
            AND s.study = $studyId
            ORDER BY c.name ASC"
        );

        return $query->getResult();

    }

    /**
     * @param PeerGroupEntity $peerGroup
     * @param StudyEntity $currentStudy
     * @return array
     */
    public function findByPeerGroup(PeerGroupEntity $peerGroup, StudyEntity $currentStudy)
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
        $qb->andWhere('s.study = :study_id');
        $qb->setParameter('study_id', $currentStudy->getId());

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

        // Filter by faculty unionized
        if ($peerGroup->getFacultyUnionized()) {
            $qb->andWhere(
                $qb->expr()->in(
                    'o.institutional_demographics_faculty_unionized',
                    ':facultyUnionized'
                )
            );
            $qb->setParameter(
                'facultyUnionized',
                $peerGroup->getFacultyUnionized()
            );
        }

        // Filter by institutional control
        if ($peerGroup->getInstitutionalControl()) {
            $qb->andWhere(
                $qb->expr()->in(
                    'o.institutional_control',
                    ':control'
                )
            );
            $qb->setParameter(
                'control',
                $peerGroup->getInstitutionalControl()
            );
        }

        // Filter by institutional type
        if ($peerGroup->getInstitutionalType()) {
            $qb->andWhere(
                $qb->expr()->in(
                    'o.institutional_type',
                    ':type'
                )
            );
            $qb->setParameter(
                'type',
                $peerGroup->getInstitutionalType()
            );
        }

        // Filter by staff unionized
        if ($peerGroup->getStaffUnionized()) {
            $qb->andWhere(
                $qb->expr()->in(
                    'o.institutional_demographics_staff_unionized',
                    ':staffUnionized'
                )
            );
            $qb->setParameter(
                'staffUnionized',
                $peerGroup->getStaffUnionized()
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

        // Filter by IPEDS fall enrollment
        if ($peerGroup->getIpedsFallEnrollment()) {
            $qb->andWhere(
                'o.ipeds_enr BETWEEN :ipeds_enrollment_min AND :ipeds_enrollment_max'
            );
            $qb->setParameter(
                'ipeds_enrollment_min',
                $peerGroup->getIpedsFallEnrollment('min')
            );
            $qb->setParameter(
                'ipeds_enrollment_max',
                $peerGroup->getIpedsFallEnrollment('max')
            );
        }

        // Filter by fiscal year student credit hours
        if ($peerGroup->getFiscalCreditHours()) {
            $qb->andWhere(
                'o.tot_fy_stud_crh BETWEEN :fysch_min AND :fysch_max'
            );
            $qb->setParameter(
                'fysch_min',
                $peerGroup->getFiscalCreditHours('min')
            );
            $qb->setParameter(
                'fysch_max',
                $peerGroup->getFiscalCreditHours('max')
            );
        }

        // Filter by pell grant recipients
        if ($peerGroup->getPellGrantRecipients()) {
            $qb->andWhere(
                'o.pell_grant_rec BETWEEN :pell_grant_min AND :pell_grant_max'
            );
            $qb->setParameter(
                'pell_grant_min',
                $peerGroup->getPellGrantRecipients('min')
            );
            $qb->setParameter(
                'pell_grant_max',
                $peerGroup->getPellGrantRecipients('max')
            );
        }

        // Filter by revenue
        if ($peerGroup->getOperatingRevenue()) {
            $qb->andWhere(
                'o.unre_o_rev BETWEEN :revenue_min AND :revenue_max'
            );
            $qb->setParameter(
                'revenue_min',
                $peerGroup->getOperatingRevenue('min')
            );
            $qb->setParameter(
                'revenue_max',
                $peerGroup->getOperatingRevenue('max')
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

        // Exclude the current college (they can't be their own peer)
        $qb->andWhere('c.id != :current_college_id');
        $qb->setParameter('current_college_id', $peerGroup->getCollege()->getId());

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

    public function delete(CollegeEntity $college)
    {
        $this->getEntityManager()->remove($college);
    }
}
