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

    /**
     * Save it with Doctrine
     *
     * @param ReportItemEntity $reportItem
     */
    public function save(ReportItemEntity $reportItem)
    {
        $this->getEntityManager()->persist($reportItem);

        $this->getEntityManager()->flush();
    }

    public function delete(ReportItemEntity $item)
    {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }
}
