<?php

namespace Mrss\Model;

use Doctrine\ORM\QueryBuilder;
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
        return $this->getRepository()->findBy(array(), array('name' => 'ASC'));
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
        $query = $em->createQuery(
            "SELECT c
            FROM Mrss\Entity\College c
            WHERE c.name LIKE ?1
            OR c.ipeds LIKE ?1
            OR c.opeId LIKE ?1
            ORDER BY c.name"
        );
        $query->setParameter(1, '%' . $term . '%');
        $query->setMaxResults($limit);

        try {
            $results = $query->getResult();

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
    public function findByCriteria($criteria, StudyEntity $currentStudy, $currentCollege, $year)
    {
        $em = $this->getEntityManager();
        $builder = $em->createQueryBuilder();

        $builder->add('select', 'c');
        $builder->add('from', '\Mrss\Entity\College c');

        // Join subscriptions
        $builder->innerJoin(
            '\Mrss\Entity\Subscription',
            's',
            'WITH',
            's.college = c.id'
        );
        $builder->andWhere('s.study = :study_id');
        $builder->setParameter('study_id', $currentStudy->getId());

        // Filter by state
        if (!empty($criteria['states']) && $states = $criteria['states']) {
            if (is_array($states) && count($states) > 0) {
                $builder->andWhere($builder->expr()->in('c.state', ':states'));
                $builder->setParameter('states', $states);
            }
        }

        // Join observations
        $builder->innerJoin(
            '\Mrss\Entity\Observation',
            'o',
            'WITH',
            's.observation = o.id'
        );

        $builder = $this->addCriteria($builder, $criteria);

        // Exclude the current college (they can't be their own peer)
        $builder->andWhere('c.id != :current_college_id');
        $builder->setParameter('current_college_id', $currentCollege->getId());

        // Filter by year
        $builder->andWhere('o.year = :year');
        $builder->setParameter('year', $year);

        // Order
        $builder->orderBy('c.name', 'ASC');

        $colleges = $builder->getQuery()->getResult();

        return $colleges;
    }

    /**
     * @param QueryBuilder $builder
     * @param $criteria
     * @return QueryBuilder
     */
    protected function addCriteria(QueryBuilder $builder, $criteria)
    {
        // Filter the the other criteria
        foreach ($criteria as $criterion => $value) {
            if ($criterion == 'states') {
                // Already handled this
                continue;
            }

            if (!empty($value)) {
                // Criteria that support multiple values, use IN
                if (is_array($value)) {
                    $builder->andWhere(
                        $builder->expr()->in(
                            "o.$criterion",
                            ':' . $criterion
                        )
                    );
                    $builder->setParameter(
                        $criterion,
                        $value
                    );

                } else {
                    // Criteria that support a range
                    $parsedRange = $this->parseRange($value);

                    $builder->andWhere(
                        "o.$criterion BETWEEN :{$criterion}_min AND :{$criterion}_max"
                    );
                    $builder->setParameter(
                        $criterion . '_min',
                        $parsedRange['min']
                    );
                    $builder->setParameter(
                        $criterion . '_max',
                        $parsedRange['max']
                    );
                }
            }
        }

        return $builder;
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
