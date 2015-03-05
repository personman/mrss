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
            $system = $user->getCollege()->getSystem();

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

            // Add the print workbook to data documentation
            $workbook = array(
                'label' => 'Print Workbook',
                'uri' => '/files/nccbp-workbook-2015.pdf',
            );
            array_unshift($pages['data-documentation']['pages'], $workbook);
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
