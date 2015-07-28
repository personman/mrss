<?php

namespace Mrss\Model;

use \Mrss\Entity\ReportItem as ReportItemEntity;

/**
 * Class ReportItem
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class ReportItem extends AbstractModel
{
    protected $entity = 'Mrss\Entity\ReportItem';

    /**
     * @param $id
     * @return \Mrss\Entity\ReportItem
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function clearCache($studyId)
    {
        $sql = "UPDATE report_items i
        JOIN reports r ON i.report_id = r.id
        SET cache = NULL
        WHERE r.study_id = :study_id";

        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        $query->execute(array('study_id' => $studyId));
    }

    /**
     * Save it with Doctrine
     *
     * @param ReportItemEntity $reportItem
     */
    public function save(ReportItemEntity $reportItem)
    {
        $this->getEntityManager()->persist($reportItem);

        //$this->getEntityManager()->flush();
    }

    public function delete(ReportItemEntity $item)
    {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }
}
