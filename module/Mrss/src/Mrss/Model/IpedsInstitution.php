<?php

namespace Mrss\Model;

use \Mrss\Entity\IpedsInstitution as IpedsInstitutionEntity;

/**
 * Class IpedsInstitution
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class IpedsInstitution extends AbstractModel
{
    protected $entity = 'Mrss\Entity\IpedsInstitution';

    public function findOneByIpeds($ipeds)
    {
        return $this->getRepository()->findOneBy(array('ipeds' => $ipeds));
    }

    public function searchByName($name, $limit = 10)
    {
        $name = strtolower($name);
        $limit = intval($limit);

        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            "SELECT i
            FROM Mrss\Entity\IpedsInstitution i
            WHERE i.name LIKE ?1
            ORDER BY i.name"
        );
        $query->setParameter(1, '%' . $name . '%');
        $query->setMaxResults($limit);

        try {
            $results = $query->getResult();
        } catch (\Exception $e) {
            return array();
        }

        return $results;
    }

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all colleges, ordered by name
     */
    public function findAll()
    {
        return $this->getRepository()->findBy(array(), array('name' => 'ASC'));
    }

    public function save(IpedsInstitutionEntity $institution)
    {
        $this->getEntityManager()->persist($institution);

        // Flush here or leave it to some other code?
    }
}
