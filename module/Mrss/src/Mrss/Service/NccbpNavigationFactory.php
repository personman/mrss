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
        $pages = parent::getPages($serviceLocator);

        // change pages here
        //unset($pages['studies']);

        return $pages;
    }
}
