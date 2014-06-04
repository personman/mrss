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
        $pages = $this->getPagesArray($serviceLocator);

        //$configuration['navigation'][$this->getName()] = array();

        $application = $serviceLocator->get('Application');
        $routeMatch  = $application->getMvcEvent()->getRouteMatch();
        $router      = $application->getMvcEvent()->getRouter();
        //$pages       = $this->getPagesFromConfig
        //($configuration['navigation'][$this->getName()]);

        $this->pages = $this->injectComponents($pages, $routeMatch, $router);

        return $this->pages;
    }

    public function getPagesArray(ServiceLocatorInterface $serviceLocator)
    {
        $pages = parent::getPages($serviceLocator);
        $currentStudy = $this->getCurrentStudy($serviceLocator);

        // Alter the nav based on auth
        $auth = $serviceLocator->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            // If the user is logged in, hide the login and subscription links
            unset($pages['login']);
            unset($pages['subscribe']);

            // And the reports preview
            unset($pages['reports_preview']);

            // Since they're logged in, also change the home page
            unset($pages['home']['route']);
            $pages['home']['uri'] = '/members';
        } else {
            // If they're logged out, hide the logout button
            unset($pages['logout']);

            // Don't show reports if they're not logged in
            unset($pages['reports']);

            // Don't show the subscribe link if enrollment/pilot isn't open
            if (!$currentStudy->getEnrollmentOpen() &&
                !$currentStudy->getPilotOpen()) {
                unset($pages['subscribe']);
            }
        }

        // Add the data entry links (if they're logged in
        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();
            $name = $user->getPrefix() . ' ' . $user->getLastName();
            $pages['account']['label'] = $name;


            if (!empty($currentStudy)) {
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

                // If data entry is disabled, rename the menu item
                if (!$currentStudy->getDataEntryOpen()) {
                    $pages['data-entry']['label'] = 'Submitted Values';
                }
            } else {
                // If there aren't any forms to show, drop the data entry menu item
                unset($pages['data-entry']);
            }
        } else {
            // Hide some pages from non-logged-in users
            unset($pages['data-entry']);
            unset($pages['account']);
            unset($pages['help']);
        }

        // Customize menu by study: Workforce
        if ($currentStudy->getId() != 3) {
            // Only show NCCET for workforce
            unset($pages['about']['pages']['nccet']);

            // Only show reports for workforce (for now)
            //unset($pages['reports']);
        }

        // Hide the partners page for non-MRSS sites
        if ($currentStudy->getId() != 2) {
            unset($pages['about']['pages']['partners']);
        }

        // MRSS
        if ($currentStudy->getId() == 2) {
            // Don't show the glossary for MRSS yet
            unset($pages['help']['pages']['glossary']);

            // Set the project name
            $pages['about2']['pages']['overview']['label'] = 'Maximizing Resources';

            unset($pages['about']['pages']);
        }

        // Workforce
        if ($currentStudy->getId() == 3) {
            // Remove partners page
            unset($pages['about2']['pages']['partners']);

            // Don't show the faq for workforce yet
            unset($pages['help']['pages']['faq']);
        }

        // If the help section is empty, drop it from the menu
        if (empty($pages['help']['pages'])) {
            unset($pages['help']);
        }

        // Hide reports link if reporting isn't enabled yet
        if (!$currentStudy->getReportsOpen() && !$currentStudy->getOutlierReportsOpen()) {
            unset($pages['reports']);
        } elseif (!$currentStudy->getOutlierReportsOpen()) {
            unset($pages['reports']['pages']['outlier']);
        } elseif (!$currentStudy->getReportsOpen()) {
            unset($pages['reports']['pages']['national']);
            unset($pages['reports']['pages']['summary']);
            unset($pages['reports']['pages']['peer']);
        }

        return $pages;
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
