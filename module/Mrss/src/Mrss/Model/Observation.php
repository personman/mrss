<?php

namespace Mrss\Model;

use \Mrss\Entity\Observation as ObservationEntity;
use Zend\Debug\Debug;

/**
 * Class Observation
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Observation extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Observation';

    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    public function findOne($collegeId, $year, $cipCode = 0)
    {
        return $this->getRepository()->findOneBy(
            array(
                'college' => $collegeId,
                'year' => $year,
                'cipCode' => $cipCode
            )
        );
    }

    public function save(ObservationEntity $observation)
    {
        $this->getEntityManager()->persist($observation);

        // Flush here or leave it to some other code?
    }
}
