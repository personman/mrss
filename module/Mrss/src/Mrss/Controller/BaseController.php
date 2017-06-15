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

        $system = null;
        if ($systemId) {
            $system = $this->getSystemModel()->find($systemId);
        }

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
        $structure = null;
        if ($this->getStudyConfig()->use_structures) {
            $currentSystem = $this->getActiveSystem();

            if ($currentSystem) {
                $structure = $currentSystem->getDataEntryStructure();
            }
        }

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

    protected function getAllBenchmarkGroups($subscription, $system = null)
    {
        if ($this->getStudyConfig()->use_structures) {

            if ($system) {
                $systems = array($system);
            } else {
                $systems = $this->getCollege()->getSystems();
            }

            $benchmarkGroups = array();
            foreach ($systems as $system) {
                $structure = $system->getReportStructure();
                $pages = $structure->getPages();

                // For multiple networks, add an optgroup for the network name
                if (false && count($systems) > 1) {
                    $optgroup = array(
                        'label' => $system->getName(),
                        'options' => array(2 => 'blah')
                    );

                    $pages = array_merge(array($system), $pages);
                }

                $benchmarkGroups = array_merge($benchmarkGroups, $pages);
            }
        } else {
            $currentStudy = $this->currentStudy();
            $benchmarkGroups = $currentStudy->getBenchmarkGroupsBySubscription($subscription);
        }

        return $benchmarkGroups;
    }

    /**
     * @return \Mrss\Entity\College
     */
    protected function getCollege()
    {
        return $this->currentCollege();
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

    /**
     * @return \Mrss\Model\SystemMembership
     */
    public function getSystemMembershipModel()
    {
        return $this->getServiceLocator()->get('model.system.membership');
    }
}
