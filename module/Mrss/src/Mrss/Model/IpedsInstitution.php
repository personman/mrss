<?php

namespace Mrss\Model;

use \Mrss\Entity\IpedsInstitution as IpedsInstitutionEntity;
use Zend\Debug\Debug;

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

    public function searchByName($term, $limit = 10)
    {
        $term = strtolower($term);
        $limit = intval($limit);

        $em = $this->getEntityManager();
        $q = $em->createQuery(
            "SELECT i
            FROM Mrss\Entity\IpedsInstitution i
            WHERE i.name LIKE ?1
            ORDER BY i.name"
        );
        $q->setParameter(1, '%' . $term . '%');
        $q->setMaxResults($limit);

        try {
            $results = $q->getResult();

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
        $c = $this->getRepository()->findBy(array(), array('name' => 'ASC'));
        return $c;
    }

    public function save(IpedsInstitutionEntity $institution)
    {
        $this->getEntityManager()->persist($institution);

        // Flush here or leave it to some other code?
    }
}
