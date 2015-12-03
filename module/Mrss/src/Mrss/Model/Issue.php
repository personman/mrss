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

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function findByCollege($college)
    {
        return $this->getRepository()->findBy(
            array(
                'college' => $college
            )
        );
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
