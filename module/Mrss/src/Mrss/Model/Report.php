<?php

namespace Mrss\Model;

use \Mrss\Entity\Report as ReportEntity;

/**
 * Class Report
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Report extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Report';

    /**
     * @param $id
     * @return \Mrss\Entity\Report
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all benchmark groups, ordered by sequence
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('name' => 'ASC'));
        return $c;
    }

    /**
     * @param $college
     * @param $study
     * @return ReportEntity[]
     */
    public function findByCollegeAndStudy($college, $study)
    {
        return $this->getRepository()->findBy(
            array(
                'college' => $college,
                'study' => $study
            ),
            array(
                'name' => 'ASC'
            )
        );
    }

    public function findByEmptyCache($studyId)
    {
        $sql = "SELECT r.id, r.name, c.name AS college
        FROM reports r
        INNER JOIN colleges c ON r.college_id = c.id
        WHERE EXISTS (SELECT i.id FROM report_items i WHERE i.cache IS NULL AND i.report_id = r.id)
        AND r.study_id = :study_id;";

        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        $query->execute(array('study_id' => $studyId));

        $results = $query->fetchAll();

        return $results;
    }

    public function findRecent($study)
    {
        return $this->getRepository()->findBy(
            array(
                'study' => $study
            ),
            array(
                'id' => 'DESC'
            ),
            10
        );
    }

    /**
     * Save it with Doctrine
     *
     * @param ReportEntity $report
     */
    public function save(ReportEntity $report)
    {
        $this->getEntityManager()->persist($report);

        $this->getEntityManager()->flush();
    }

    public function delete(ReportEntity $report)
    {
        $this->getEntityManager()->remove($report);
        $this->getEntityManager()->flush();
    }
}
