<?php

namespace Mrss\Model;

use \Mrss\Entity\System as SystemEntity;
use Zend\Debug\Debug;

/**
 * Class System
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class System extends AbstractModel
{
    protected $entity = 'Mrss\Entity\System';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function findByName($name)
    {
        return $this->getRepository()->findOneBy(array('name' => $name));
    }

    /**
     * Find all systems, ordered by name
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('name' => 'ASC'));
        return $c;
    }


    public function save(SystemEntity $system)
    {
        $this->getEntityManager()->persist($system);

        // Flush here or leave it to some other code?
    }
}
