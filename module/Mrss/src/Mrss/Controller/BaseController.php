<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;

class BaseController extends AbstractActionController
{
    protected $activeSystemContainer;

    public function getActiveSystemContainer()
    {
        if (empty($this->activeSystemContainer)) {
            $container = new Container('active_system');
            $this->activeSystemContainer = $container;
        }

        return $this->activeSystemContainer;
    }

    public function setActiveSystem($systemId)
    {
        $this->getActiveSystemContainer()->system_id = $systemId;
    }

    public function getActiveSystem()
    {
        $systemId = $this->getActiveSystemContainer()->system_id;

        // If none is set yet, just, uh, grab one
        if (empty($systemId)) {
            foreach ($this->currentCollege()->getSystemMemberships() as $systemMembership) {
                // The first one will do
                $systemId = $systemMembership->getSystem()->getId();
            }
        }

        return $systemId;
    }

    /**
     * @return \Mrss\Model\System
     */
    public function getSystemModel()
    {
        return $this->getServiceLocator()->get('model.system');
    }
}
