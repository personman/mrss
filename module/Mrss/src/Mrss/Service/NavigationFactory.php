<?php

namespace Mrss\Service;

use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Entity\Study;
use Mrss\Entity\User;
use Mrss\Model\Subscription as SubscriptionModel;
use BjyAuthorize\Service\Authorize;
use Zend\Session\Container;

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

    /** @var SubscriptionModel */
    protected $subscriptionModel;

    protected $serviceLocator;

    protected $isAdmin;

    public function getPages(ServiceLocatorInterface $serviceLocator)
    {
        $pages = $this->getPagesArray($serviceLocator);
        $this->serviceLocator = $serviceLocator;

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
        $this->serviceLocator = $serviceLocator;

        $pages = parent::getPages($serviceLocator);
        $currentStudy = $this->getCurrentStudy($serviceLocator);

        // Alter the nav based on auth
        $auth = $serviceLocator->get('zfcuser_auth_service');
        $user = null;
        $impersonationService = $this->getImpersonationService();

        $system = null;

        if ($auth->hasIdentity()) {
            // If the user is logged in, hide the login and subscription links
            unset($pages['login']);
            unset($pages['subscribe']);

            // And the reports preview
            unset($pages['reports_preview']);

            // Since they're logged in, also change the home page
            if (!empty($pages['home'])) {
                unset($pages['home']['route']);
                $pages['home']['uri'] = '/members';
            }

            // Do they belong to a system?
            $user = $auth->getIdentity();
            if ($user && $college = $user->getCollege()) {
                try {
			 $system = $college->getSystem();
		} catch (\Exception $e) {
		}
            }
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

            // Logged out users can't renew
            unset($pages['renew']);

            // Data doc
            unset($pages['data-documentation']);
        }


        // Rename or hide system report link (only show for NCCBP)
        // @todo: generalize
        if ($system && in_array($currentStudy->getId(), array(1, 4))) {
            $label = $system->getName() . ' Report';
            $pages['reports']['pages']['system']['label'] = $label;
        } else {
            unset($pages['reports']['pages']['system']);
        }



        if ($auth->hasIdentity() && $user = $auth->getIdentity()) {

            // Add the data entry links (if they're logged in

            $name = $user->getPrefix() . ' ' . $user->getLastName();
            $pages['account']['label'] = $name;

            // Logged in users don't need demos
            unset($pages['schedule-demo']);

            if (!empty($currentStudy)) {
                /*
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
                            'benchmarkGroup' => $bGroup->getUrl()
                        )
                    );
                }

                $pages['data-entry']['pages'] = $dataEntryPages;

                // If data entry is disabled, rename the menu item
                if (!$currentStudy->getDataEntryOpen()) {
                    $pages['data-entry']['label'] = 'Submitted Values';
                }
                */
            } else {
                // If there aren't any forms to show, drop the data entry menu item
                unset($pages['data-entry']);
            }

            // Hide data entry menu item if data entry is closed
            if (!$currentStudy->getDataEntryOpen()) {
                unset($pages['data-entry']);
            }

            // Hide reports menu if they only have one year (this year) and reports aren't open
            $study = $this->getCurrentStudy();
            if (!$study->getReportsOpen()) {
                $subs = $this->getSubscriptions();
                if (count($subs) == 1 && $subs[0]->getYear() == $study->getCurrentYear()) {
                    unset($pages['reports']);
                }
            }

            $enrollmentOpen = $this->getCurrentStudy()->getEnrollmentOpen();

            // If enrollment is open and they haven't subscribed, show renew button
            if ($enrollmentOpen && !$this->hasSubscription($user)) {
                // Show renew
                // Hide data entry
                unset($pages['data-entry']);
            } else {
                // Hide it
                unset($pages['renew']);
            }

            // Hide the button for adding modules to the membership if needed
            if ($this->hasSubscription() && $enrollmentOpen && $this->getSubscription()->canBeUpdated($study)) {
                // Keep it
            } else {
                unset($pages['account']['pages']['membership-edit']);
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
        } else {
            // Hide the executive report
            unset($pages['executive']);
            unset($pages['reports']['pages']['executive']);

        }

        // Hide the partners page for non-MRSS sites
        if ($currentStudy->getId() != 2) {
            unset($pages['about']['pages']['partners']);
        }

        // Hide data issues from all non-AAUP studies
        if ($currentStudy->getId() != 4) {
            unset($pages['admin']['pages']['issues/staff']);
        }

        // Since it's the first year of aaup, don't show the report menu until open and paid
        if ($currentStudy->getId() == 4) {
            $college = $this->getCollege();
            if ($college && $college->hasReportAccess()) {
                // Allow the reports menu to show (hide the public results menu)
                unset($pages['members-results']);
            } else {
                // Hide it.
                unset($pages['reports']);
            }
        }

        // MRSS
        if ($currentStudy->getId() == 2) {
            // Don't show best performers yet
            unset($pages['reports']['pages']['best-performers']);
            unset($pages['reports']['pages']['high-low']);

            // Don't show the glossary for MRSS yet
            unset($pages['help']['pages']['glossary']);

            // Set the project name
            $pages['about2']['pages']['overview']['label'] = 'Maximizing Resources';

            unset($pages['about']['pages']);

            // Hide reports for MRSS since it's never had them open
            // The rest of the studies retain the links for prior years
            //if ((empty($user) || $user->getCollege()->getId() != 101)
            //    /*&& !$impersonationService->isImpersonated()*/) {
            //    unset($pages['reports']);
            //}

            // Hide the reports that MRSS doesn't yet use
            unset($pages['reports']['pages']['peer']);
            unset($pages['reports']['pages']['summary']);
            unset($pages['reports']['pages']['strengths']);

            // Hide some reports if they're disabled
            if (!$currentStudy->getReportsOpen()) {
                unset($pages['reports']['pages']['national']);
                unset($pages['reports']['pages']['custom']);
            }

            // Hide the executive report
            unset($pages['executive']);
            unset($pages['reports']['pages']['executive']);

            // Hide all max reports (except institutional)
            //$newReports = array($pages['reports']['pages']['institutional']);
            //$pages['reports']['pages'] = $newReports;
            //unset($pages['reports']);
        } else {
            // The institutional report is Max only. Hide it from the others
            unset($pages['reports']['pages']['institutional']);
        }

        // Workforce
        if ($currentStudy->getId() == 3) {
            // Don't show best performers yet
            unset($pages['reports']['pages']['best-performers']);
            unset($pages['reports']['pages']['high-low']);

            // Institutional report is for MRSS only
            unset($pages['reports']['institutional']);

            // Remove partners page
            unset($pages['about2']['pages']['partners']);

            // Don't show the faq for workforce yet
            unset($pages['data-documentation']['pages']['faq']);

            // Hide the executive report
            unset($pages['executive']);

            // No strengths report for WF
            unset($pages['reports']['pages']['strengths']);

            // No percent change report
            unset($pages['reports']['pages']['percent-change']);
        }

        // Envisio (@todo: move this to Envisio module)
        if ($study->getId() == 5) {
            // No percent change report
            unset($pages['reports']['pages']['percent-change']);

            // Change title of national report
            $pages['reports']['pages']['national']['label'] = 'Benchmark Report';
        }

        // If the help section is empty, drop it from the menu
        if (empty($pages['help']['pages'])) {
            unset($pages['help']);
        }

        // Show all reports for JCCC users
        /*if ((!empty($user) && $user->getCollege()->getId() != 101)
            && !$impersonationService->isImpersonated()) {
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
        }*/

        // Hide outliers if closed
        if (!empty($user)) {
            if (!$currentStudy->getOutlierReportsOpen()) {
                unset($pages['reports']['pages']['outlier']);
            }
        }



        // Check permissions
        $authorizeService = $this->getAuthorizeService();

        // Admin menu is done through config via integration with zend nav
        // @todo: move these others there, too.

        // Hide data entry from those who aren't allowed
        if (!$authorizeService->isAllowed('dataEntry', 'view')) {
            unset($pages['data-entry']);
        }

        // Hide membership management from those who aren't allowed
        if (!$authorizeService->isAllowed('membership', 'view')) {
            unset($pages['account']['pages']['institution']);
            unset($pages['account']['pages']['users']);
            unset($pages['renew']);
        }

        return $pages;
    }

    public function getImpersonationService()
    {
        return $this->serviceLocator
            ->get('zfcuserimpersonate_user_service');
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

    public function setSubscriptionModel(SubscriptionModel $model)
    {
        $this->subscriptionModel = $model;

        return $this;
    }

    public function getSubscriptionModel()
    {
        if (empty($this->subscriptionModel)) {
            if (!empty($this->serviceLocator)) {
                $this->subscriptionModel = $this->serviceLocator
                    ->get('model.subscription');
            }
        }

        return $this->subscriptionModel;
    }

    protected function getSubscription()
    {
        $subModel = $this->getSubscriptionModel();
        $study = $this->getCurrentStudy();
        $year = $study->getCurrentYear();

        $subscription = null;

        if ($user = $this->getUser()) {
            $systemId = $this->getSystemCollegeId();
            $user = $this->getUser();
            $collegeId = $user->getCollege()->getId();

            if ($systemId && $systemId != $collegeId) {
                $collegeId = $systemId;
            }


            $subscription = $subModel->findOne($year, $collegeId, $study->getId());
        }

        return $subscription;
    }

    protected function getSubscriptions()
    {
        $subModel = $this->getSubscriptionModel();
        $study = $this->getCurrentStudy();

        $subscriptions = null;

        if ($user = $this->getUser()) {
            $systemId = $this->getSystemCollegeId();
            $user = $this->getUser();
            $collegeId = $user->getCollege()->getId();

            if ($systemId && $systemId != $collegeId) {
                $collegeId = $systemId;
            }


            $subscriptions = $subModel->findByCollegeAndStudy($collegeId, $study->getId());
        }

        return $subscriptions;
    }

    protected function hasSubscription()
    {
        $subscription = $this->getSubscription();

        return (!empty($subscription));
    }

    protected function getSystemCollegeId()
    {
        $container = new Container('system_admin');

        if (!empty($container->college)) {
            return $container->college;
        }
    }

    /**
     * @return Authorize
     */
    protected function getAuthorizeService()
    {
        $authorizeService = $this->serviceLocator->get('BjyAuthorizeServiceAuthorize');

        return $authorizeService;
    }

    /**
     * @return
     */
    protected function getAuthService()
    {
        return $this->serviceLocator->get('zfcuser_auth_service');
    }

    protected function getUser()
    {
        $user = null;
        $auth = $this->getAuthService();

        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();
        }

        return $user;
    }

    /**
     * @return null|\Mrss\Entity\College
     */
    protected function getCollege()
    {
        $college = null;

        if ($user = $this->getUser()) {
            $college = $user->getCollege();
        }

        return $college;
    }
}
