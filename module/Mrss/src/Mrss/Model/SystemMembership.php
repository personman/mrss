<?php

namespace Mrss\Model;

use \Mrss\Entity\SystemMembership as SystemMembershipEntity;
use Doctrine\ORM\Query;

/**
 * Class SystemMembership
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class SystemMembership extends AbstractModel
{
    protected $entity = 'Mrss\Entity\SystemMembership';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function findBySystemCollegeYear($system, $college, $year)
    {
        return $this->getRepository()->findOneBy(array(
            'system' => $system,
            'college' => $college,
            'year' => $year
        ));
    }

    public function findByCollegeYear($college, $year)
    {
        return $this->getRepository()->findOneBy(array(
            'college' => $college,
            'year' => $year
        ));
    }

    public function save(SystemMembershipEntity $system)
    {
        $this->getEntityManager()->persist($system);

        // Flush here or leave it to some other code?
    }

    public function delete(SystemMembershipEntity $entity)
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

}
