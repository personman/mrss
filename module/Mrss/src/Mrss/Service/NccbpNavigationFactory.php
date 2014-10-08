<?php

namespace Mrss\Service;

use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class NccbpNavigationFactory extends DefaultNavigationFactory
{
    public function getName()
    {
        return 'nccbp';
    }

    public function getPages(ServiceLocatorInterface $serviceLocator)
    {
        $auth = $serviceLocator->get('zfcuser_auth_service');
        $user = null;
        $system = null;
        if ($auth->hasIdentity()) {
            $user = $auth->getIdentity();
            $system = $user->getCollege()->getSystem();
        }

        $pages = parent::getPages($serviceLocator);

        if ($system) {
            $label = $system->getName() . ' Report';
            $pages['reports']['pages']['system']['label'] = $label;
        } else {
            unset($pages['reports']['pages']['system']);
        }


        if ($auth->hasIdentity()) {

            // Add the data entry links (if they're logged in
            $user = $auth->getIdentity();
            $name = $user->getPrefix() . ' ' . $user->getLastName();
            $pages['account']['label'] = $name;
        } else {
            unset($pages['account']);
        }

        // change pages here
        //unset($pages['studies']);

        return $pages;
    }
}
