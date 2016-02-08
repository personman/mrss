<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\ObservationAudit as Audit;

class ObservationAudit implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $service = new Audit;
        $userService = $sm->get('zfcuser_auth_service');
        $impersonationService = $sm->get('zfcuserimpersonate_user_service');

        // Set the current User
        $user = $userService->getIdentity();
        $service->setUser($user);

        // If there's an admin impersonating this user, pass that
        if ($impersonationService->isImpersonated()) {
            $impersonator = $impersonationService
                ->getStorageForImpersonator()->read();

            $service->setImpersonator($impersonator);
        }

        // The current study
        $currentStudy = $sm->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $service->setStudy($currentStudy);

        // The benchmark model
        $service->setBenchmarkModel($sm->get('model.benchmark'));

        // Changeset model
        $service->setChangeSetModel($sm->get('model.changeSet'));

        return $service;
    }
}
