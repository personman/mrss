<?php

namespace Mrss\Service;

use Zend\ServiceManager\ServiceLocatorInterface;

class FcsNavigationFactory extends NavigationFactory
{
    public function getName()
    {
        return 'aaup';
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
                $system = $college->getSystem();
            }

            // Remove the Home button from nav if user is role: viewer
            if ($user->getRole() == 'viewer') {
                $pages['home']['uri'] = '/reports/executive';
            }
        }

        // If the user is logged in, hide some stuff
        if ($auth->hasIdentity()) {
            unset($pages['join']);
            unset($pages['about']);
            unset($pages['survey']);
            unset($pages['results']);
            unset($pages['resources']);
            unset($pages['contact']);
        } else {
            unset($pages['members-about']);
            unset($pages['data-collection']);
            unset($pages['documentation']);
            unset($pages['members-results']);
            unset($pages['members-resources']);
            unset($pages['members-contact']);
        }

        // Add individual forms
        $dataEntryPages = array();

        // Now add each form
        $currentStudy = $this->getCurrentStudy($serviceLocator);
        foreach ($currentStudy->getBenchmarkGroups() as $bGroup) {
            $dataEntryPages[] = array(
                'label' => $bGroup->getName(),
                'route' => 'data-entry/edit',
                'params' => array(
                    'benchmarkGroup' => $bGroup->getUrl()
                )
            );
        }

        $dataEntryPages = array_merge($pages['data-collection']['pages'], $dataEntryPages);

        $pages['data-collection']['pages'] = $dataEntryPages;

        // Hide data entry links, if needed
        if (!$this->getAuthorizeService()->isAllowed('dataEntry', 'view')) {
            unset($pages['data-collection']);
        }

        return $pages;
    }
}
