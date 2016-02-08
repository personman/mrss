<?php

namespace Mrss\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Mrss\Service\Validation as Validate;

class Validation implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sm)
    {
        $service = new Validate();
        $currentStudy = $sm->get('ControllerPluginManager')
            ->get('currentStudy')->getCurrentStudy();
        $service->setStudy($currentStudy);

        // Find the validator class in the study config
        $studyConfig = $sm->get('study');
        if ($class = $studyConfig->validation_class) {
            $class = "Mrss\\Service\\$class";
            $validator = new $class;
            $service->setValidator($validator);
        }

        // Set the user
        $userService = $sm->get('zfcuser_auth_service');
        $user = $userService->getIdentity();
        $service->setUser($user);

        // Set the issue model
        $issueModel = $sm->get('model.issue');
        $service->setIssueModel($issueModel);

        return $service;
    }
}
