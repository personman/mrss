<?php

namespace Mrss\Model;

use \Mrss\Entity\ChangeSet as ChangeSetEntity;

/**
 * Class ChangeSet
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class ChangeSet extends AbstractModel
{
    protected $entity = 'Mrss\Entity\ChangeSet';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all
     */
    public function findAll()
    {
        $c = $this->getRepository()->findAll();
        return $c;
    }

    public function findByStudy($studyId, $limit = 1000)
    {
        return $this->getRepository()->findBy(
            array(
                'study' => $studyId
            ),
            array('date' => 'DESC'),
            $limit
        );
    }

    public function findByStudyAndCollege($studyId, $college)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT cs
            FROM Mrss\Entity\ChangeSet cs
            INNER JOIN MRSS\Entity\User u
            WHERE u.college = :college
            AND cs.study = :study
            ORDER BY cs.date DESC"
        );
        $query->setParameter('college', $college);
        $query->setParameter('study', $studyId);

        $results = $query->getResult();

        return $results;
    }

    public function save(ChangeSetEntity $changeSet)
    {
        $this->getEntityManager()->persist($changeSet);
    }

    public function delete(ChangeSetEntity $changeSet)
    {
        $this->getEntityManager()->remove($changeSet);
        $this->getEntityManager()->flush();
    }
}
