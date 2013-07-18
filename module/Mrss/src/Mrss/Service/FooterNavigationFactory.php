<?php

namespace Mrss\Service;

use Mrss\Service\NavigationFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class FooterNavigationFactory extends NavigationFactory
{
    public function getPages(ServiceLocatorInterface $serviceLocator)
    {
        $pages = $this->getPagesArray($serviceLocator);

        $confidentiality = array(
            'label' => 'Confidentiality',
            'uri' => 'confidentiality'
        );

        $nhebi = array(
            'label' => 'NHEBI',
            'title' => 'National Higher Education Benchmarking Institute',
            'uri' => 'http://www.nccbp.org/national-higher-education-benchmarking-institute'
        );

        // Add this in second to last:
        // First, chop off the last item
        $last = array_pop($pages);
        array_push($pages, $confidentiality);
        array_push($pages, $nhebi);
        array_push($pages, $last);

        //$configuration['navigation'][$this->getName()] = array();

        $application = $serviceLocator->get('Application');
        $routeMatch  = $application->getMvcEvent()->getRouteMatch();
        $router      = $application->getMvcEvent()->getRouter();
        //$pages       = $this->getPagesFromConfig
        //($configuration['navigation'][$this->getName()]);

        $this->pages = $this->injectComponents($pages, $routeMatch, $router);

        return $this->pages;
    }
}
