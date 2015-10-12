<?php

namespace Mrss\Model;

use Mrss\Entity\Criterion as CriterionEntity;
use Zend\Debug\Debug;

/**
 * Class Criterion
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Criterion extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Criterion';

    public function findOneByName($name)
    {
        return $this->getRepository()->findOneBy(array('name' => $name));
    }

    public function findOneByShortName($shortName)
    {
        return $this->getRepository()->findOneBy(array('shortName' => $shortName));
    }

    /**
     * @param $url
     * @return BenchmarkGroupEntity
     */
    public function findOneByUrlAndStudy($url, $study)
    {
        return $this->getRepository()->findOneBy(array('url' => $url, 'study' => $study));
    }

    /**
     * @param $id
     * @return \Mrss\Entity\BenchmarkGroup
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all benchmark groups, ordered by sequence
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('sequence' => 'ASC'));
        return $c;
    }

    /**
     * Save it with Doctrine
     *
     * @param CriterionEntity $criterion
     */
    public function save(CriterionEntity $criterion)
    {
        // Confirm that the sequence is set
        if (!$criterion->getSequence()) {
            $max = $this->getMaxSequence($criterion->getStudy());
            $newMax = $max + 1;
            $criterion->setSequence($newMax);
        }

        $this->getEntityManager()->persist($criterion);

        // Flush here or leave it to some other code?
    }

    public function getMaxSequence($study)
    {
        $lastCriterion = $this->getRepository()->findOneBy(
            array('study' => $study),
            array('sequence' => 'DESC')
        );

        if (!empty($lastCriterion)) {
            $max = $lastCriterion->getSequence();
        } else {
            $max = 1;
        }

        return $max;
    }
}
