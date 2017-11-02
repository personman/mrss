<?php

namespace Mrss\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Mrss\Entity\College;
use Mrss\Model\College as CollegeModel;
use Zend\Session\Container;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication as UserPlugin;

/**
 * Class CurrentCollege
 *
 * The college we are working with.
 *
 * @package Mrss\Controller\Plugin
 */
class CurrentCollege extends AbstractPlugin
{
    /**
     * @var CollegeModel
     */
    protected $collegeModel;

    /**
     * @var UserPlugin
     */
    protected $userPlugin;

    /**
     * @return College
     */
    public function __invoke()
    {
        return $this->getCurrentCollege();
    }

    public function getCurrentCollege()
    {
        $user = $this->getuserPlugin()->getIdentity();

        if (empty($user)) {
            return null;
        }

        if ($user->getRole() == 'system_admin' || $user->getRole() == 'system_viewer'
            && !empty($this->getSystemAdminSessionContainer()->college)) {

            $college = $this->getCollegeModel()->find(
                $this->getSystemAdminSessionContainer()->college
            );
        } else {
            $college = $user->getCollege();
        }

        return $college;
    }

    /**
     * @param CollegeModel $collegeModel
     * @returns CurrentCollege
     */
    public function setCollegeModel($collegeModel)
    {
        $this->collegeModel = $collegeModel;

        return $this;
    }

    /**
     * @return CollegeModel
     */
    public function getCollegeModel()
    {
        return $this->collegeModel;
    }

    /**
     * @param UserPlugin $userPlugin
     */
    public function setuserPlugin($userPlugin)
    {
        $this->userPlugin = $userPlugin;
    }

    /**
     * @returns UserPlugin
     */
    public function getuserPlugin()
    {
        return $this->userPlugin;
    }
    
    public function getSystemAdminSessionContainer()
    {
        $container = new Container('system_admin');
        
        return $container;
    }
}
