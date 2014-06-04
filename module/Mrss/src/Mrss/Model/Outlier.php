<?php

namespace Mrss\Model;

use Mrss\Entity\Outlier as OutlierEntity;
use Mrss\Entity\Study as StudyEntity;
use Mrss\Entity\College as CollegeEntity;
use Zend\Debug\Debug;

/**
 * Class Outlier
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Outlier extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Outlier';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all systems, ordered by name
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * Order not working on this one
     *
     * @deprecated
     * @param StudyEntity $study
     * @return array
     */
    public function findByStudy(StudyEntity $study)
    {
        $year = $study->getCurrentYear();
        $studyId = $study->getId();

        $query = $this->getEntityManager()->createQuery(
            "SELECT o
            FROM Mrss\Entity\Outlier o
            JOIN Mrss\Entity\College college
            WHERE o.study = $studyId
            AND o.year = $year
            ORDER BY college.name ASC"
        );

        return $query->getResult();
    }

    public function findByCollegeStudyAndYear(
        CollegeEntity $college,
        StudyEntity $study,
        $year
    ) {
        return $this->getRepository()->findBy(
            array(
                'college' => $college,
                'study' => $study,
                'year' => $year
            )
        );
    }

    public function save(OutlierEntity $outlier)
    {
        $this->getEntityManager()->persist($outlier);
    }

    public function delete(OutlierEntity $outlier)
    {
        $this->getEntityManager()->remove($outlier);
        $this->getEntityManager()->flush();
    }

    public function deleteByStudyAndYear($studyId, $year)
    {
        $query = $this->getEntityManager()->createQuery(
            'DELETE Mrss\Entity\Outlier p WHERE p.year = ?1 AND p.study = ?2'
        );
        $query->setParameter(1, $year);
        $query->setParameter(2, $studyId);

        $query->execute();
    }
}
