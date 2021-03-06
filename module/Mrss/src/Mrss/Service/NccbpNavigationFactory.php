<?php

namespace Mrss\Service;

use Zend\ServiceManager\ServiceLocatorInterface;

class NccbpNavigationFactory extends NavigationFactory
{
    public function getName()
    {
        return 'nccbp';
    }

    public function getPagesArray(ServiceLocatorInterface $serviceLocator)
    {
        $pages = parent::getPagesArray($serviceLocator);

        $auth = $serviceLocator->get('zfcuser_auth_service');
        $user = null;
        $system = null;
        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();
            if ($college = $user->getCollege()) {
                try {
                    $systemMemberships = $college->getSystemMemberships();
                } catch (\Exception $e) {
                }
            }


            // Remove the Home button from nav if user is role: viewer
            if ($user->getRole() == 'viewer') {
                $pages['home']['uri'] = '/reports/executive';
            }
        }

        // If the user is logged in, hide some stuff
        if ($auth->hasIdentity()) {
            unset($pages['benchmarks']);
            unset($pages['reports-overview']);
            unset($pages['who-we-help']);
            unset($pages['join']);
            unset($pages['contact']['pages']);
            unset($pages['about']);
            unset($pages['help']);

            if (true) {
                // Add the print workbook to data documentation (disabled for now)
                $workbook = array(
                    'label' => 'Print Workbook',
                    //'uri' => '/files/nccbp-workbook-2017.pdf',
                    'route' => 'data-entry/print',
                    'params' => array('year' => $this->getCurrentStudy()->getCurrentYear())
                );
                array_unshift($pages['data-documentation']['pages'], $workbook);
            }

            // Show non-credit report?
            $subscription = $this->getSubscription(true);
            if (!$subscription || !$subscription->hasSection(2)) {
                unset($pages['reports']['pages']['non-credit']);
            }

            // Social mobility report
            if (true) {
                $mobility = array(
                    'label' => 'Social Mobility Report',
                    'uri' => '/reports/social-mobility'
                );

                if (!empty($pages['reports'])) {
                    $pages['reports']['pages']['social-mobility'] = $mobility;
                }
            }

            // Chat
            if (empty($this->getStudyConfig()->muut)) {
                unset($pages['chat']);
            }
        } else {
            unset($pages['help']);
        }

        // Hide the summary report
        unset($pages['reports']['pages']['summary']);

        // Hide the institutional report
        unset($pages['reports']['pages']['institutional']);

        // Hide submitted values as NCCBP handles them under data documentation
        if (!empty($pages['data-entry'])) {
            if ($pages['data-entry']['label'] == 'Submitted Values') {
                unset($pages['data-entry']);
            }
        }


        /*if ($auth->hasIdentity()) {

            // Add the data entry links (if they're logged in
            $user = $auth->getIdentity();
            $name = $user->getPrefix() . ' ' . $user->getLastName();
            $pages['account']['label'] = $name;
        } else {
            unset($pages['account']);
        }*/

        // change pages here
        //unset($pages['studies']);

        return $pages;
    }
}
