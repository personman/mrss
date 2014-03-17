<?php

namespace Mrss\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Session\Container;
use Mrss\Model\College;

/**
 * Class SystemActiveCollege
 *
 * Find the active college for the system user and return it
 *
 * @package Mrss\Controller\Plugin
 */
class SystemActiveCollege extends AbstractPlugin
{
    /**
     * @var Container
     */
    protected $session;

    /**
     * @var College
     */
    protected $collegeModel;

    /**
     * @return \Mrss\Entity\College
     */
    public function __invoke()
    {
        return $this->getActiveCollege();
    }

    public function getActiveCollege()
    {
        if (!empty($this->getSessionContainer()->college)) {
            $collegeId = $this->getSessionContainer()->college;
            $college = $this->getCollegeModel()->find($collegeId);

            if (!empty($college)) {
                return $college;
            }
        }

        return false;
    }

    public function setSessionContainer(Container $container)
    {
        $this->session = $container;

        return $this;
    }

    public function getSessionContainer()
    {
        return $this->session;
    }

    public function setCollegeModel(College $collegeModel)
    {
        $this->collegeModel = $collegeModel;

        return $this;
    }

    public function getCollegeModel()
    {
        return $this->collegeModel;
    }
}
