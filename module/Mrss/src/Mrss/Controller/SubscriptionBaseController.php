<?php

namespace Mrss\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Mrss\Entity\SystemMembership as SystemMembershipEntity;
use Mrss\Entity\College as CollegeEntity;
use Mrss\Form\SubscriptionAdmin;
use Mrss\Entity\Subscription;
use Mrss\Entity\Observation;
use Zend\Session\Container;
use Mrss\Entity\Criterion;
use Mrss\Entity\Study;
use Zend\Mail\Message;
use Mrss\Entity\User;
use PHPExcel;

class SubscriptionBaseController extends BaseController
{
    protected $sessionContainer;

    protected $passwordService;

    protected $draftModel;

    protected $log;

    /**
     * @var \Mrss\Entity\Study
     */
    protected $study;

    protected function getAdminForm($collegeId)
    {
        $systemOptions = array();
        if (true || $this->getStudyConfig()->use_structures) {
            foreach ($this->getSystemModel()->findAll() as $system) {
                $systemOptions[$system->getId()] = $system->getName();
            }
        }

        $systemLabel = $this->getStudyConfig()->system_label;

        $sectionOptions = array();
        if ($sections = $this->currentStudy()->getSections()) {
            foreach ($sections as $section) {
                $sectionOptions[$section->getId()] = $section->getName();
            }
        }

        $freemium = $this->getStudyConfig()->freemium;

        $form = new SubscriptionAdmin($systemOptions, $systemLabel, $sectionOptions, $freemium);

        $form->get('collegeId')->setValue($collegeId);

        return $form;
    }

    public function adminAddAction()
    {
        $collegeId = $this->params()->fromRoute('college');
        $currentStudy = $this->currentStudy();
        $college = $this->getCollegeModel()->find($collegeId);

        $form = $this->getAdminForm($collegeId);

        if ($this->getRequest()->isPost()) {
            // Handle the form
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $year = $data['year'];
                $collegeId = $data['collegeId'];
                $systems = $data['systems'];

                // First, see if the subscription exists
                $subscription = $this->getSubscriptionModel()->findOne($year, $collegeId, $currentStudy->getId());

                // If it doesn't exist, create it
                if (!$subscription) {
                    $subscription = new Subscription();
                    $subscription->setYear($year);
                    $subscription->setStatus('complete');
                    $subscription->setCollege($college);
                    $subscription->setStudy($currentStudy);
                    $subscription->setObservation($this->createOrUpdateObservation($college, $year));
                    $subscription->setCompletion(0);
                    $subscription->setReportAccess(true);
                    $subscription->setFree($data['free']);

                    $this->getSubscriptionModel()->save($subscription);
                    $this->getSubscriptionModel()->getEntityManager()->flush();
                }

                // Now, either way, update the system memberships, too
                $this->updateSystemMemberships($college, $systems, $year);

                $this->updateSections($subscription, $this->params()->fromPost('modules'));

                // Message and redirect
                $systemLabel = $this->getStudyConfig()->system_label;
                $systemLabel = ucwords($systemLabel);
                $this->flashMessenger()->addSuccessMessage("$systemLabel membership saved.");
                $this->redirect()->toRoute('colleges/view', array('id' => $collegeId));
            }
        } else {
            // Set the default year
            $form->get('year')->setValue(date('Y'));
        }

        return array(
            'form' => $form,
            'college' => $college
        );
    }

    protected function createOrUpdateObservation(CollegeEntity $college, $year = null)
    {
        if (!$year) {
            $year = $this->getCurrentYear();
        }

        /** @var \Mrss\Model\Observation $observationModel */
        $observationModel = $this->getServiceLocator()->get('model.observation');

        $observation = $observationModel->findOne(
            $college->getId(),
            $year
        );

        if (empty($observation)) {
            $observation = new Observation;
            $observation->setMigrated(false);
        }

        $observation->setYear($year);
        $observation->setCollege($college);

        $observationModel->save($observation);

        return $observation;
    }

    protected function updateSections(Subscription $subscription, $sections)
    {
        $sections = $this->getSectionsByIds($sections);
        $subscription->updateSections($sections);
    }

    protected function getSectionsByIds($sectionIds)
    {
        $sections = array();
        foreach ($sectionIds as $sectionId) {
            $sections[] = $this->getStudy()->getSection($sectionId);
        }

        return $sections;
    }

    protected function updateSystemMemberships(CollegeEntity $college, $systemIds, $year)
    {
        $existingSystemIds = $college->getSystemIdsByYear($year);

        foreach ($systemIds as $systemId) {
            // Does it exist already?
            $system = $this->getSystemModel()->find($systemId);
            $membership = $this->getSystemMembershipModel()
                ->findBySystemCollegeYear($system, $college, $year);

            if (!$membership) {
                // Create it
                $membership = new SystemMembershipEntity();
                $membership->setCollege($college);
                $membership->setSystem($system);
                $membership->setYear($year);
                $membership->setDataVisibility('public');

                $this->getSystemMembershipModel()->save($membership);
            }

            // Remove from existing list since this doesn't need deletion
            if (($key = array_search($systemId, $existingSystemIds)) !== false) {
                unset($existingSystemIds[$key]);
            }
        }

        // Anything left should be removed
        foreach ($existingSystemIds as $toDelete) {
            $membership = $this->getSystemMembershipModel()
                ->findBySystemCollegeYear($toDelete, $college, $year);

            if ($membership) {
                $this->getSystemMembershipModel()->delete($membership);
            } else {
                //die('cannot find');
            }
        }

        $this->getEntityManager()->flush();
    }

    public function deleteAction()
    {
        // Load the subscription
        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getSubscriptionModel();

        $subscriptionId = $this->params()->fromRoute('id');

        if ($subscriptionId == 'all') {
            takeYourTime();

            return $this->deleteAllSubscriptions();
        } else {
            $subscription = $subscriptionModel->find($subscriptionId);
        }


        // If the subscription's not found, redirect them
        if (empty($subscription)) {
            $this->flashMessenger()->addErrorMessage(
                'Unable to find subscription with id: ' . $subscriptionId
            );

            return $this->redirect()->toUrl('/admin');
        }

        $message = $this->deleteSubscription($subscription);


        $this->flashMessenger()->addSuccessMessage($message);
        return $this->redirect()->toUrl('/admin');
    }

    protected function deleteAllSubscriptions()
    {
        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();
        if ($year = $this->params()->fromQuery('year')) {
            $subscriptions = $study->getSubscriptionsForYear($year);
        } else {
            $subscriptions = $study->getSubscriptions();
        }

        $message = '';
        foreach ($subscriptions as $subscription) {
            $message .= $this->deleteSubscription($subscription) . "<br>\n";
        }

        $this->flashMessenger()->addSuccessMessage($message);
        return $this->redirect()->toUrl('/admin');
    }

    protected function deleteSubscription(Subscription $subscription)
    {
        /** @var \Mrss\Model\Observation $observationModel */
        $observationModel = $this->getServiceLocator()->get('model.observation');

        // Load the subscription
        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');

        $message = '';
        // See if this is the college's last subscription
        $college = $subscription->getCollege();
        if (count($college->getSubscriptions()) == 1) {
            // This college only has one subscription: the one we're deleting.
            // Delete the college and its users and observations
            /** @var \Mrss\Model\College $collegeModel */
            $collegeModel = $this->getServiceLocator()->get('model.college');

            /** @var \Mrss\Model\User $userModel */
            $userModel = $this->getServiceLocator()->get('model.user');

            // Delete users
            foreach ($college->getUsers() as $user) {
                $userModel->delete($user);
            }

            // Delete observations
            foreach ($college->getObservations() as $observation) {
                $observationModel->delete($observation);
            }

            // Delete college
            $collegeModel->delete($college);

            $message .= "Since {$college->getName()} only has this one subscription,
            the college and its users have been deleted. ";
        } else {
            $observation = $subscription->getObservation();

            if ($observation) {
                // Should we delete this year's observation
                $subscriptions = $observation->getSubscriptions();
                if (count($subscriptions) == 1) {
                    // This is the only subscription using the observation, so axe it
                    $observationModel->delete($observation);
                    $message .= "Observation deleted. ";
                } else {
                    // This college has other subscriptions. Don't delete the users or obs
                    // But do clear out their data for fields not in other studies
                    $benchmarkKeysToNull = $this->getBenchmarkKeysInThisStudyOnly(
                        $observation
                    );

                    foreach ($benchmarkKeysToNull as $dbColumn) {
                        $observation->set($dbColumn, null);
                    }

                    $observationModel->save($observation);

                    $count = count($benchmarkKeysToNull);
                    $message .= "$count fields cleared out from this year's observation. ";
                }
            }
        }

        // Delete the subscription row
        $subscriptionModel->delete($subscription);
        $subscriptionModel->getEntityManager()->flush();

        $message .= "Subscription deleted. ";

        return $message;
    }


    protected function getBenchmarkKeysInThisStudyOnly(Observation $observation)
    {
        /** @var \Mrss\Entity\Study $currentStudy */
        $currentStudy = $this->getStudy();

        $subscriptions = $observation->getSubscriptions();
        $allStudies = array();
        foreach ($subscriptions as $subscription) {
            $allStudies[] = $subscription->getStudy();
        }

        $benchmarksInStudy = $currentStudy->getAllBenchmarkKeys();


        $inStudyOnly = $benchmarksInStudy;

        foreach ($allStudies as $study) {
            /** @var Study $study */
            if ($study->getId() == $currentStudy->getId()) {
                continue;
            }

            $inStudyOnly = array_diff(
                $inStudyOnly,
                $study->getAllBenchmarkKeys()
            );
        }

        return $inStudyOnly;
    }

    protected function getAllColleges()
    {
        $colleges = $this->getCollegeModel()->findAll();

        $allColleges = array();
        foreach ($colleges as $college) {
            $nameAndState = $college->getName() . ' (' . $college->getState() . ')';
            if ($ipeds = $college->getIpeds()) {
                $nameAndState .= ', IPEDS: ' . $ipeds;
            }
            if ($opeId = $college->getOpeId()) {
                $nameAndState .= ', OPE: ' . $opeId;
            }
            $allColleges[] = array(
                'label' => $nameAndState,
                'id' => $college->getId()
            );
        }

        return json_encode($allColleges);
    }

    protected function getCurrentYear()
    {
        return $this->getStudy()->getCurrentYear();
    }

    /**
     * @param $institutionForm
     * @return CollegeEntity
     */
    protected function createOrUpdateCollege($institutionForm)
    {
        $collegeModel = $this->getCollegeModel();
        $college = $collegeModel->findOneByIpeds($institutionForm['ipeds']);

        if (empty($college)) {
            $college = new CollegeEntity;
            $needFlush = true;
        }

        $hydrator = new DoctrineHydrator(
            $this->getEntityManager(),
            'Mrss\Entity\College'
        );

        /** @var CollegeEntity $college */
        $college = $hydrator->hydrate($institutionForm, $college);
        $collegeModel->save($college);

        if (!empty($needFlush)) {
            // Flush so we'll have an id
            $this->getEntityManager()->flush();
        }

        return $college;
    }

    protected function createOrUpdateUser($data, $role, $college, $state = null)
    {
        $email = $data['email'];

        /** @var \Mrss\Model\User $userModel */
        $userModel = $this->getServiceLocator()->get('model.user');

        $user = $userModel->findOneByEmail($email);

        if (empty($user)) {
            $user = new User;
            $createUser = true;

            // State (0 = pending, 1 = active, 2 = disabled)
            if ($state !== null) {
                $user->setState($state);
            }
        }

        $user->setCollege($college);
        $user->setEmail($email);
        $user->setPrefix($data['prefix']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setTitle($data['title']);
        $user->setPhone($data['phone']);
        $user->setExtension($data['extension']);
        $user->addStudy($this->getStudy());


        // 111111
        $user->setPassword('$2y$14$uCp4wgvaHPpvq/.Z3yvtzu7VLuKSphIROS8dLHEAduOo5LaZpvUnC');

        // set role
        $user->setRole($role);


        $userModel->save($user);

        // Flush to db so id is set
        $this->getEntityManager()->flush();

        if (!empty($createUser)) {
            // If they're already approved, send them a password reset link
            if ($state == 1) {
                $this->sendPasswordResetEmail($user);
            } else {
                // If they're not approved, notify the approver
                $this->notifyApprover($user);
            }
        }

        return $user;
    }

    /**
     * Get the study that they're subscribing to
     *
     * @throws \Exception
     * @return \Mrss\Entity\Study
     */
    protected function getStudy()
    {
        if (empty($this->study)) {
            $studyId = $this->currentStudy()->getId();

            /** @var \Mrss\Model\Study $studyModel */
            $studyModel = $this->getServiceLocator()->get('model.study');
            $this->study = $studyModel->find($studyId);

            if (empty($this->study)) {
                throw new \Exception("Study with id $studyId not found.");
            }
        }

        return $this->study;
    }



    protected function notifyApprover(User $user)
    {
        $studyConfig = $this->getStudyConfig();
        $approverEmail = $studyConfig->approver_email;
        $fromEmail = $studyConfig->from_email;

        $email = new Message();
        $email->addFrom($fromEmail);
        $email->addTo($approverEmail);

        $study = $this->currentStudy();
        $studyName = $study->getName();

        $collegeName = $user->getCollege()->getName();

        $email->setSubject("New user pending for $studyName");
        $userName = $user->getFullName();
        $url = $this->getServiceLocator()->get('ViewHelperManager')->get('serverUrl')
            ->__invoke('/users/queue');

        $body = "
            Name: $userName
            Email: {$user->getEmail()}
            Institution: $collegeName

            Approve users: $url
            ";

        $email->setBody($body);

        $this->getServiceLocator()->get('mail.transport')->send($email);
    }

    /**
     * @param User $user
     * @throws \Exception
     */
    protected function sendPasswordResetEmail($user)
    {
        $pwService = $this->getPasswordService();
        $pwService->getOptions()
            ->setResetEmailTemplate('email/subscription/newuser');
        $pwService->getOptions()->setResetEmailSubjectLine(
            'Welcome to ' . $this->getStudy()->getDescription()
        );

        $pwService->sendProcessForgotRequest($user->getId(), $user->getEmail());
    }

    /**
     * @return array|\GoalioForgotPassword\Service\Password
     */
    protected function getPasswordService()
    {
        if (!$this->passwordService) {
            $this->passwordService = $this->getServiceLocator()
                ->get('goalioforgotpassword_password_service');
        }
        return $this->passwordService;
    }

    protected function saveTransIdToSession($transId)
    {
        $this->getSessionContainer()->transId = $transId;
    }

    protected function getTransIdFromSession()
    {
        return $this->getSessionContainer()->transId;
    }

    protected function getSessionContainer()
    {
        if (empty($this->sessionContainer)) {
            $this->sessionContainer = new Container('subscribe');
        }

        return $this->sessionContainer;
    }


    /**
     * @return \Mrss\Model\SubscriptionDraft
     */
    protected function getSubscriptionDraftModel()
    {
        if (empty($this->draftModel)) {
            $this->draftModel = $this->getServiceLocator()
                ->get('model.subscription.draft');
        }

        return $this->draftModel;
    }

    public function downloadAction()
    {
        takeYourTime();

        $year = $this->params()->fromRoute('year');

        $subscriptionsInfo = $this->getSubscriptionsForExport($year);

        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->fromArray($subscriptionsInfo);

        foreach (range(0, count($subscriptionsInfo[0])) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        // Make the first row bold
        $sheet->getStyle('A1:Z1')->getFont()->setBold(true);

        $filename = $this->currentStudy()->getName() . '-Members-' . $year;

        header(
            'Content-Type: '.
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');
        die;
    }

    /**
     * These are dbColumns for benchmarks that are selected as demographic criteria but that shouldn't
     * be included in them ember download (NCCBP only).
     * @return array
     */
    protected function getCriteriaToSkipForDownload()
    {
        return array(
            'CFI'
        );
    }

    /**
     * @param Study $study
     * @return Criterion[]
     */
    protected function getCriteria(Study $study)
    {
        $criteria = array();
        foreach ($study->getCriteria() as $criterion) {
            if (!in_array($criterion->getBenchmark()->getDbColumn(), $this->getCriteriaToSkipForDownload())) {
                $criteria[] = $criterion;
            }
        }

        return $criteria;
    }

    protected function getSubscriptionsForExport($year)
    {
        $model = $this->getSubscriptionModel();

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $subscriptionsInfo = array();

        // Should we limit this by system/network?
        if ($this->getStudyConfig()->use_structures) {
            $system = $this->getActiveSystem();
            $colleges = $system->getMemberColleges();

            $subscriptions = array();
            foreach ($colleges as $college) {
                /** @var CollegeEntity $collegeEntity */
                $collegeEntity = $college['college'];
                $subscriptions[] = $collegeEntity->getSubscriptionByStudyAndYear(
                    $study->getId(),
                    $year
                );
            }
        } else {
            $subscriptions = $model->findByStudyAndYear($study->getId(), $year);
        }

        $headers = array($this->getStudyConfig()->institution_label, 'State', 'IPEDS Unit ID');

        if ($this->getStudy()->hasSections()) {
            $headers[] = 'Modules';
        }

        foreach ($this->getCriteria($study) as $criterion) {
            $headers[] = $criterion->getName();
        }

        $subscriptionsInfo[] = $headers;


        foreach ($subscriptions as $sub) {
            $college = $sub->getCollege();
            $observation = $sub->getObservation();

            $exportRow = array(
                $college->getName(),
                $college->getState(),
                $college->getIpeds(),
            );

            if ($this->getStudy()->hasSections()) {
                $exportRow[] = $sub->getSectionNames();
            }

            foreach ($this->getCriteria($study) as $criterion) {
                $exportRow[] = $observation->get($criterion->getBenchmark()->getDbColumn());
            }

            $subscriptionsInfo[] = $exportRow;
        }

        return $subscriptionsInfo;
    }

    public function reportAccessAction()
    {
        $subscriptionId = $this->params()->fromPost('subscriptionId');
        $enabled = $this->params()->fromPost('checked');

        if ($subscriptionId && $subscription = $this->getSubscriptionModel()->find($subscriptionId)) {
            $enabled = !empty($enabled);
            $subscription->setReportAccess($enabled);

            $this->getSubscriptionModel()->save($subscription);
            $this->getSubscriptionModel()->getEntityManager()->flush();

            $responseText = 'ok';
        } else {
            $responseText = 'Membership not found.';
        }

        $response = $this->getResponse()->setContent($responseText);
        return $response;
    }
}
