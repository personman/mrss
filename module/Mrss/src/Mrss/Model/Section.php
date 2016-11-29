<?php

namespace Mrss\Model;

use \Mrss\Entity\Section as SectionEntity;

/**
 * Class Section
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Section extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Section';

    /**
     * @param $sectionId
     * @return SectionEntity
     */
    public function find($sectionId)
    {
        return $this->getRepository()->find($sectionId);
    }

    /**
     * Find all benchmark groups, ordered by sequence
     * @return SectionEntity[]
     */
    public function findAll()
    {
        return $this->getRepository()->findBy(array(), array('name' => 'ASC'));
    }

    /**
     * Find all sections for a study
     * @param $studyId
     * @return SectionEntity[]
     */
    public function findByStudy($studyId)
    {
        return $this->getRepository()->findBy(
            array(
                'study' => $studyId
            )
        );
    }

    /**
     * Save it with Doctrine
     *
     * @param SectionEntity $section
     */
    public function save(SectionEntity $section)
    {
        $this->getEntityManager()->persist($section);

        $this->getEntityManager()->flush();
    }
}
