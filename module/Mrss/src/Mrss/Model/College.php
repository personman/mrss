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

    /**
     * @param $opeId
     * @return \Mrss\Entity\College|null
     */
    public function findOneByOpeId($opeId)
    {
        return $this->getRepository()->findOneBy(array('opeId' => $opeId));
    }

    /**
     * @param $id
     * @return null|\Mrss\Entity\College
     */
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

    /**
     * @param array $ids
     * @return \Mrss\Entity\College[]
     */
    public function findByIds($ids)
    {
        return $this->getRepository()->findBy(
            array('id' => $ids),
            array('name' => 'ASC')
        );
    }

    /**
     * @param array $state
     * @return \Mrss\Entity\College[]
     */
    public function findByState($state)
    {
        return $this->getRepository()->findBy(
            array('state' => $state),
            array('name' => 'ASC')
        );
    }

    /**
     * @param StudyEntity $study
     * @param $year
     * @return \Mrss\Entity\College[]
     */
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

    public function findByNameAndIdentifiers($term, $limit = 10)
    {
        $term = strtolower($term);
        $limit = intval($limit);

        $em = $this->getEntityManager();
        $q = $em->createQuery(
            "SELECT c
            FROM Mrss\Entity\College c
            WHERE c.name LIKE ?1
            OR c.ipeds LIKE ?1
            OR c.opeId LIKE ?1
            ORDER BY c.name"
        );
        $q->setParameter(1, '%' . $term . '%');
        $q->setMaxResults($limit);

        try {
            $results = $q->getResult();

        } catch (\Exception $e) {
            return array();
        }

        return $results;
    }

    protected function parseRange($range)
    {
        $parts = explode('-', $range);
        $min = intval(trim($parts[0]));
        $max = intval(trim($parts[1]));

        return array(
            'min' => $min,
            'max' => $max
        );
    }

    /**
     * @param $criteria
     * @param StudyEntity $currentStudy
     * @param $currentCollege
     * @return \Mrss\Entity\College[]
     */
    public function findByCriteria($criteria, StudyEntity $currentStudy, $currentCollege)
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
        $qb->andWhere('s.study = :study_id');
        $qb->setParameter('study_id', $currentStudy->getId());

        // Filter by state
        if ($states = $criteria['states']) {
            if (is_array($states) && count($states) > 0) {
                $qb->andWhere($qb->expr()->in('c.state', ':states'));
                $qb->setParameter('states', $states);
            }
        }

        // Join observations
        $qb->innerJoin(
            '\Mrss\Entity\Observation',
            'o',
            'WITH',
            's.observation = o.id'
        );


        // Filter the the other criteria
        foreach ($criteria as $criterion => $value) {
            if ($criterion == 'states') {
                // Already handled this
                continue;
            }

            if (!empty($value)) {
                // Criteria that support multiple values, use IN
                if (is_array($value)) {
                    $qb->andWhere(
                        $qb->expr()->in(
                            "o.$criterion",
                            ':' . $criterion
                        )
                    );
                    $qb->setParameter(
                        $criterion,
                        $value
                    );

                } else {
                    // Criteria that support a range
                    $parsedRange = $this->parseRange($value);

                    $qb->andWhere(
                        "o.$criterion BETWEEN :{$criterion}_min AND :{$criterion}_max"
                    );
                    $qb->setParameter(
                        $criterion . '_min',
                        $parsedRange['min']
                    );
                    $qb->setParameter(
                        $criterion . '_max',
                        $parsedRange['max']
                    );
                }
            }
        }

        // Exclude the current college (they can't be their own peer)
        $qb->andWhere('c.id != :current_college_id');
        $qb->setParameter('current_college_id', $currentCollege->getId());

        // Order
        $qb->orderBy('c.name', 'ASC');


        if (false) {
            $dql = $qb->getDQL();
            //var_dump($dql);
            $p = $qb->getParameters();
            //var_dump($p);

            $colleges = $qb->getQuery()->getResult();
            $count = count($colleges);

            //var_dump($count);

            foreach ($colleges as $college) {
                pr($college->getName());
            }
            //var_dump($colleges);

            //die('findByPeerGroup');
            //var_dump($qb); die;*/
        }

        $colleges = $qb->getQuery()->getResult();

        return $colleges;
    }

    /**
     * @param PeerGroupEntity $peerGroup
     * @param StudyEntity $currentStudy
     * @deprecated
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
        //$qb->where('s.year = :year');
        //$qb->setParameter('year', $peerGroup->getYear());
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

        // Filter by four year degrees
        if ($peerGroup->getFourYearDegrees()) {
            $qb->andWhere(
                $qb->expr()->in(
                    'o.four_year_degrees',
                    ':fourYearDegrees'
                )
            );
            $qb->setParameter(
                'fourYearDegrees',
                $peerGroup->getFourYearDegrees()
            );
        }

        // Filter by on-campus housing
        if ($peerGroup->getOnCampusHousing()) {
            $qb->andWhere(
                $qb->expr()->in(
                    'o.on_campus_housing',
                    ':onCampusHousing'
                )
            );
            $qb->setParameter(
                'onCampusHousing',
                $peerGroup->getOnCampusHousing()
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

        // Filter by % blk
        if ($peerGroup->getBlk()) {
            $qb->andWhere(
                'o.blk_2012 BETWEEN :blk_min AND :blk_max'
            );
            $qb->setParameter(
                'blk_min',
                $peerGroup->getBlk('min')
            );
            $qb->setParameter(
                'blk_max',
                $peerGroup->getBlk('max')
            );
        }

        // Filter by % asian
        if ($peerGroup->getAsian()) {
            $qb->andWhere(
                'o.asian_2012 BETWEEN :asian_min AND :asian_max'
            );
            $qb->setParameter(
                'asian_min',
                $peerGroup->getAsian('min')
            );
            $qb->setParameter(
                'asian_max',
                $peerGroup->getAsian('max')
            );
        }

        // Filter by % hispanic
        if ($peerGroup->getHispAnyrace()) {
            $qb->andWhere(
                'o.hisp_anyrace_2012 BETWEEN :hisp_min AND :hisp_max'
            );
            $qb->setParameter(
                'hisp_min',
                $peerGroup->getHispAnyrace('min')
            );
            $qb->setParameter(
                'hisp_max',
                $peerGroup->getHispAnyrace('max')
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

        // Filter by technical credit hours
        if ($peerGroup->getTechnicalCredit()) {
            $qb->andWhere(
                'o.t_c_crh
                BETWEEN :technical_min AND :technical_max'
            );
            $qb->setParameter(
                'technical_min',
                $peerGroup->getTechnicalCredit('min')
            );
            $qb->setParameter(
                'technical_max',
                $peerGroup->getTechnicalCredit('max')
            );
        }

        // Filter by full-time students percentage
        if ($peerGroup->getPercentageFullTime()) {
            $qb->andWhere(
                'o.ft_cr_head_perc
                BETWEEN :full_time_min AND :full_time_max'
            );
            $qb->setParameter(
                'full_time_min',
                $peerGroup->getPercentageFullTime('min')
            );
            $qb->setParameter(
                'full_time_max',
                $peerGroup->getPercentageFullTime('max')
            );
        }

        // Exclude the current college (they can't be their own peer)
        $qb->andWhere('c.id != :current_college_id');
        $qb->setParameter('current_college_id', $peerGroup->getCollege()->getId());

        // Order
        $qb->orderBy('c.name', 'ASC');


        if (false) {
            $dql = $qb->getDQL();
            //var_dump($dql);
            $p = $qb->getParameters();
            //var_dump($p);

            $colleges = $qb->getQuery()->getResult();
            $count = count($colleges);

            //var_dump($count);
            //var_dump($colleges);

            pr($peerGroup->getFourYearDegrees());
            pr($peerGroup->getOnCampusHousing());
            //die('findByPeerGroup');
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
