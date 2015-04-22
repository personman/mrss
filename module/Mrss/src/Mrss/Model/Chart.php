<?php

namespace Mrss\Model;

use \Mrss\Entity\Chart as ChartEntity;
use \Mrss\Entity\College as CollegeEntity;
use \Mrss\Entity\Study as StudyEntity;
use Zend\Debug\Debug;

/**
 * Class Chart
 *
 * This model should present a nice API to the controllers and other models.
 * The fact that Doctrine is used for persistence should be seen as an
 * implementation detail. Other classes shouldn't know or care about that.
 *
 * @package Mrss\Model
 */
class Chart extends AbstractModel
{
    protected $entity = 'Mrss\Entity\Chart';

    /**
     * @param $id
     * @return null|\Mrss\Entity\Chart
     */
    public function find($id)
    {
        return $this->getRepository()->find($id);
    }

    /**
     * Find all colleges, ordered by name
     * @return \Mrss\Entity\Chart[]
     */
    public function findAll()
    {
        $c = $this->getRepository()->findBy(array(), array('name' => 'ASC'));
        return $c;
    }

    /**
     * @param StudyEntity $study
     * @param \Mrss\Entity\College $college
     * @return \Mrss\Entity\College[]
     */
    public function findByStudyAndCollege(StudyEntity $study, CollegeEntity $college)
    {
        $c = $this->getRepository()->findBy(
            array('study' => $study, 'college' => $college),
            array('name' => 'ASC')
        );

        return $c;
    }

    /**
     * @param StudyEntity $study
     * @param \Mrss\Entity\College $college
     * @param $name
     * @return \Mrss\Entity\College[]
     */
    public function findByStudyCollegeAndName(StudyEntity $study, CollegeEntity $college, $name)
    {
        $c = $this->getRepository()->findOneBy(
            array('study' => $study, 'college' => $college, 'name' => $name)
        );

        return $c;
    }

    public function save(ChartEntity $chart)
    {
        $this->getEntityManager()->persist($chart);

        // Flush here or leave it to some other code?
    }

    public function delete(ChartEntity $chart)
    {
        $this->getEntityManager()->remove($chart);
    }
}
