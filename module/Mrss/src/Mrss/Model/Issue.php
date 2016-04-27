<?php

namespace Mrss\Model;

use Mrss\Entity\Issue as IssueEntity;
use Mrss\Model\AbstractModel;

/**
 * Class Issue
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Cms\Model
 */
class Issue extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Issue';

    /**
     * @param $id
     * @return null|IssueEntity
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param $college
     * @return IssueEntity[]
     */
    public function findByCollege($college)
    {
        return $this->getRepository()->findBy(
            array(
                'college' => $college
            )
        );
    }

    /**
     * Returns issues with null status for given college
     * @param $college
     * @return IssueEntity[]
     */
    public function findForCollege($college)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT i
            FROM Mrss\Entity\Issue i
            WHERE i.status IS NULL
            AND i.college = :college
            ORDER BY i.status DESC"
        );

        $query->setParameter('college', $college);

        $results = $query->getResult();

        return $results;
    }

    /**
     * @param array $include
     * @param array $exclude
     * @param bool|true $includeNull
     * @return IssueEntity[]
     */
    public function findByStatus($include = array(), $exclude = array(), $includeNull = true)
    {
        $where = '';
        if ($include) {
            $where = 'WHERE status IN (:include) ';
        }

        if ($includeNull) {
            $includeNull = ' OR i.status IS NULL';
        }

        if ($exclude) {
            if ($where) {
                $where .= ' AND ';
            } else {
                $where .= 'WHERE ';
            }

            $where .= "(i.status NOT IN (:exclude) $includeNull)";
        }

        $em = $this->getEntityManager();
        $query = $em->createQuery(
            "SELECT i
            FROM Mrss\Entity\Issue i
            $where
            ORDER BY i.status DESC"
        );

        if ($include) {
            $query->setParameter('include', $include);
        }
        if ($exclude) {
            $query->setParameter('exclude', $exclude);
        }

        $results = $query->getResult();

        /*
        try {
            $results = $query->getResult();

        } catch (\Exception $e) {
            return array();
        }*/

        return $results;
    }

    /**
     * Find all pages, ordered by title
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    public function save(IssueEntity $issue)
    {
        $this->getEntityManager()->persist($issue);
    }

    public function delete(IssueEntity $issue)
    {
        $this->getEntityManager()->remove($issue);
        $this->getEntityManager()->flush();
    }

    public function deleteByCollege($college)
    {
        foreach ($this->findByCollege($college) as $issue) {
            $this->delete($issue);
        }
    }
}
