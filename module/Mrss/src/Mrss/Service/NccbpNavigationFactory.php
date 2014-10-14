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
        $auth = $serviceLocator->get('zfcuser_auth_service');
        $user = null;
        $system = null;
        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();
            $system = $user->getCollege()->getSystem();
        }

        //$pages = parent::getPages($serviceLocator);
        $pages = parent::getPagesArray($serviceLocator);

        // If the user is logged in, hide some stuff
        if ($auth->hasIdentity()) {
            unset($pages['nccbp']);
            unset($pages['reports-overview']);
            unset($pages['who-we-help']);
            unset($pages['join']);
            unset($pages['contact']['pages']);
            unset($pages['about']);
        } else {
            unset($pages['home']);
            unset($pages['help']);
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
