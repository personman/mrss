<?php

namespace Mrss\Model;

use \Mrss\Entity\College as CollegeEntity;
use Zend\Debug\Debug;

/**
 * Class College
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class College extends AbstractModel
{
    protected $entity = 'Mrss\Entity\College';

    public function findOneByIpeds($ipeds)
    {
        return $this->getRepository()->findOneBy(array('ipeds' => $ipeds));
    }

    /**
     * Find all colleges, ordered by name
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('name' => 'ASC'));
        return $c;
    }

    public function save(CollegeEntity $college)
    {
        $this->getEntityManager()->persist($college);

        // Flush here or leave it to some other code?
    }
}
