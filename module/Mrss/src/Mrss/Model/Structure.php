<?php

namespace Mrss\Model;

use Mrss\Entity\Structure as StructureEntity;

/**
 * Class Structure
 *
 * @package Mrss\Model
 */
class Structure extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Structure';

    /**
     * @param $id
     * @return \Mrss\Entity\Structure
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function save(StructureEntity $structure)
    {
        $this->getEntityManager()->persist($structure);

        // Flush elsewhere
    }
}
