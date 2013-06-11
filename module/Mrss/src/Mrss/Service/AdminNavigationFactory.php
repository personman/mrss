<?php

namespace Mrss\Service;

use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdminNavigationFactory extends DefaultNavigationFactory
{
    public function getName()
    {
        return 'admin';
    }

    /*public function getPages(ServiceLocatorInterface $serviceLocator)
    {
        $pages = parent::getPages($serviceLocator);

        // change pages here

        return $pages;
    }*/
}
