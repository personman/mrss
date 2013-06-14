<?php

namespace Mrss\Service;

use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Entity\Study;

/**
 * Class NavigationFactory
 *
 * @todo: Cache this. Will need to be at the college level as it changes for each
 * college
 *
 * @package Mrss\Service
 */
class NavigationFactory extends DefaultNavigationFactory
{
    /** @var Study */
    protected $currentStudy;

    public function getPages(ServiceLocatorInterface $serviceLocator)
    {
        $pages = parent::getPages($serviceLocator);

        // Alter the nav based on auth
        $auth = $serviceLocator->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            // If the user is logged in, hide the login and subscription links
            unset($pages['login']);
            unset($pages['subscribe']);
        } else {
            // If they're logged out, hide the logout button
            unset($pages['logout']);
        }

        // Add the data entry links (if they're logged in
        if ($auth->hasIdentity()) {
            if ($currentStudy = $this->getCurrentStudy($serviceLocator)) {
                $dataEntryPages = array();

                // Add the overview page
                $dataEntryPages[] = array(
                    'label' => 'Overview',
                    'route' => 'data-entry'
                );

                // Now add each form
                foreach ($currentStudy->getBenchmarkGroups() as $bGroup) {
                    $dataEntryPages[] = array(
                        'label' => $bGroup->getName(),
                        'route' => 'data-entry/edit',
                        'params' => array(
                            'benchmarkGroup' => $bGroup->getId()
                        )
                    );
                }

                $pages['data-entry']['pages'] = $dataEntryPages;
            } else {
                // If there aren't any forms to show, drop the data entry menu item
                unset($pages['data-entry']);
            }
        } else {
            unset($pages['data-entry']);
        }


        //$configuration['navigation'][$this->getName()] = array();

        $application = $serviceLocator->get('Application');
        $routeMatch  = $application->getMvcEvent()->getRouteMatch();
        $router      = $application->getMvcEvent()->getRouter();
        //$pages       = $this->getPagesFromConfig
        //($configuration['navigation'][$this->getName()]);

        $this->pages = $this->injectComponents($pages, $routeMatch, $router);

        return $this->pages;
    }

    public function setCurrentStudy(Study $study)
    {
        $this->currentStudy = $study;

        return $this;
    }

    public function getCurrentStudy($serviceLocator = null)
    {
        if (empty($this->currentStudy) && !empty($serviceLocator)) {
            $plugin = $serviceLocator
                ->get('ControllerPluginManager')
                ->get('currentStudy');

            $this->currentStudy = $plugin->getCurrentStudy();
        }

        return $this->currentStudy;
    }
}
