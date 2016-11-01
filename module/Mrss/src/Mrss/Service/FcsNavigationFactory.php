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
        if ($auth->hasIdentity() && $user = $auth->getIdentity()) {

            /*if ($college = $user->getCollege()) {
                $system = $college->getSystem();
            }*/

            // Remove the Home button from nav if user is role: viewer
            if ($user->getRole() == 'viewer') {
                $pages['home']['uri'] = '/reports/executive';
            }

            // If the user is logged in, hide some stuff
            unset($pages['join']);
            unset($pages['about']);
            unset($pages['start']);
            unset($pages['results']);
            unset($pages['resources']);
            unset($pages['contact']);

            // Hide data entry if there's no subscription
            if (!$this->hasSubscription($user)) {
                unset($pages['data-collection']);
            }
        } else {
            unset($pages['members-about']);
            unset($pages['data-collection']);
            unset($pages['documentation']);
            unset($pages['members-results']);
            unset($pages['members-resources']);
            unset($pages['members-contact']);
        }

        $pages = $this->addDataCollectionForms($pages, $serviceLocator);

        return $pages;
    }

    protected function addDataCollectionForms($pages, $serviceLocator)
    {
        // Hide data entry links, if needed
        if (!$this->getAuthorizeService()->isAllowed('dataEntry', 'view')) {
            unset($pages['data-collection']);
        }

        // Add individual forms
        $dataEntryPages = array();

        // Now add each form
        if (isset($pages['data-collection'])) {
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
        }

        return $pages;
    }
}
