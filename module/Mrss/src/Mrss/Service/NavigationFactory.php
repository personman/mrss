<?php

namespace Mrss\Service;

use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class NavigationFactory extends DefaultNavigationFactory
{
    public function getPages(ServiceLocatorInterface $serviceLocator)
    {
        $pages = parent::getPages($serviceLocator);

        // Alter the nav based on auth
        $auth = $serviceLocator->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            // If the user is logged in, hide the login button
            unset($pages['login']);
        } else {
            // If they're logged out, hide the logout button
            unset($pages['logout']);
        }

        return $pages;
    }
}
