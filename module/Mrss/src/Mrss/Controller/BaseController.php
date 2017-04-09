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
        $systemId = $this->getActiveSystemId();
        $system = $this->getSystemModel()->find($systemId);

        return $system;
    }
    public function getActiveSystemId()
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

    protected function getStudyConfig()
    {
        return $this->getServiceLocator()->get('Study');
    }

    /**
     * @return \Mrss\Entity\Structure
     */
    protected function getStructure()
    {
        $currentSystem = $this->getActiveSystem();


        $structure = $currentSystem->getDataEntryStructure();

        return $structure;
    }

    protected function getBenchmarkGroups($subscription)
    {
        if ($this->getStudyConfig()->use_structures) {
            $structure = $this->getStructure();
            $benchmarkGroups = $structure->getPages();

        } else {
            $currentStudy = $this->currentStudy();
            $benchmarkGroups = $currentStudy->getBenchmarkGroupsBySubscription($subscription);
        }

        return $benchmarkGroups;
    }

    protected function getMembership()
    {
        $year = $this->currentStudy()->getCurrentYear();
        $membership = $this->getSubscriptionModel()->findOne(
            $year,
            $this->currentCollege()->getId(),
            $this->currentStudy()->getId()
        );

        return $membership;
    }

    /**
     * @return \Mrss\Model\System
     */
    public function getSystemModel()
    {
        return $this->getServiceLocator()->get('model.system');
    }
}
