<?php

namespace Mrss\Model;

use \Mrss\Entity\Observation as ObservationEntity;
use Zend\Debug\Debug;

/**
 * Class Observation
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Observation extends AbstractModel
{
    /**
     * The entity we're working with
     *
     * @var string
     */
    protected $entity = 'Mrss\Entity\Observation';

    /**
     * Find an observation by its id
     *
     * @param $id
     * @return null|object
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find an observation by college, year, cipcode
     *
     * @param $collegeId
     * @param $year
     * @param int $cipCode
     * @return null|object
     */
    public function findOne($collegeId, $year, $cipCode = 0)
    {
        return $this->getRepository()->findOneBy(
            array(
                'college' => $collegeId,
                'year' => $year,
                'cipCode' => $cipCode
            )
        );
    }

    /**
     * Get all the observations
     *
     * @return array
     */
    public function findAll()
    {
        return $this->getRepository()->findAll(
            array(),
            array(
                'year' => 'ASC',
                'college_id' => 'ASC'
            )
        );
    }

    /**
     * Return data for a single benchmark, multiple colleges, multiple years
     */
    public function findForChart($benchmarkColumn, $collegeIds)
    {
        // Headers based on colleges
        $selects = array('year');
        $headers = array('Year');
        foreach ($collegeIds as $collegeId) {
            $selects[] = "MAX( IF( college_id = $collegeId, $benchmarkColumn,
            NULL)) AS c$collegeId";
            $headers[] = "c" . $collegeId;
        }

        // Prepare a queryBuilder
        $connection = $this->getEntityManager()->getConnection();
        $connection->setFetchMode(\PDO::FETCH_NUM);
        $qb = $connection->createQueryBuilder();

        // The query
        $qb->select($selects);
        $qb->from('observations', 'o');
        $qb->groupBy('year');

        $data = $qb->execute()->fetchAll();

        //return array_merge(array($headers), $data);
        return $data;
    }

    /**
     * Save an observation
     *
     * @param ObservationEntity $observation
     */
    public function save(ObservationEntity $observation)
    {
        $this->getEntityManager()->persist($observation);

        // Flush here or leave it to some other code?
    }
}
