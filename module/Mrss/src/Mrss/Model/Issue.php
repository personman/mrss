<?php

namespace Mrss\Model;

use Mrss\Entity\Issue as IssueEntity;

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
     * @param $issueId
     * @return null|IssueEntity
     */
    public function find($issueId)
    {
        return $this->getRepository()->find($issueId);
    }

    /**
     * @param $college
     * @param $year
     * @return IssueEntity[]
     */
    public function findByCollege($college, $year = null)
    {
        $params = array(
            'college' => $college
        );

        if ($year) {
            $params['year'] = $year;
        }

        return $this->getRepository()->findBy($params);
    }

    /**
     * Returns issues with null status for given college
     * @param $college
     * @return IssueEntity[]
     */
    public function findForCollege($college)
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
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

        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
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
