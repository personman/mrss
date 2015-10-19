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
        if (!empty($criteria['states']) && $states = $criteria['states']) {
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


        /*if (false) {
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
            //var_dump($qb); die;
        }*/

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
