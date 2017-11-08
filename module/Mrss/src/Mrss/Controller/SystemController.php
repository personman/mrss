<?php

namespace Mrss\Controller;

use Mrss\Entity\SystemMembership;
use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\System as SystemForm;
use Mrss\Entity\System;
use Mrss\Entity\Structure;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\View\Model\ViewModel;

class SystemController extends AbstractActionController
{
    /**
     * Show the current systems
     *
     * @return array|void
     */
    public function indexAction()
    {
        $systemModel = $this->getSystemModel();

        return array(
            'systems' => $systemModel->findAll()
        );
    }

    /**
     * @return \Mrss\Model\System
     */
    public function getSystemModel()
    {
        return $this->getServiceLocator()->get('model.system');
    }

    public function viewAction()
    {

        $collegeModel = $this->getServiceLocator()->get('model.college');
        $id = $this->params('id');

        if (empty($id)) {
            throw new \Exception('You cannot view a system without the id.');
        }

        $system = $this->getSystemModel()->find($id);

        if (empty($system)) {
            throw new \Exception('System not found.');
        }

        $this->populateStructures($system);

        return array(
            'system' => $system,
            'colleges' => $collegeModel->findAll()
        );
    }

    protected function populateStructures(System $system)
    {
        $systemModel = $this->getSystemModel();

        // Create structures, if needed
        $studyConfig = $this->getServiceLocator()->get('study');
        if ($studyConfig->system_benchmarks) {
            if (!$system->getDataEntryStructure()) {
                $structure = new Structure();
                $system->setDataEntryStructure($structure);
                $systemModel->save($system);
                $systemModel->getEntityManager()->flush();
            }

            if (!$system->getReportStructure()) {
                $structure = new Structure();
                $system->setReportStructure($structure);
                $systemModel->save($system);
                $systemModel->getEntityManager()->flush();
            }
        }
    }

    public function addAction()
    {
        $label = $this->getServiceLocator()->get('study')->system_label;

        $form = new SystemForm($label);

        $id = $this->params('id');
        if (empty($id) && $this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id');
        }

        $system = $this->getSystem($id);
        $form->setInputFilter($system->getInputFilter());

        $form->setHydrator(
            new DoctrineHydrator(
                $this->getServiceLocator()->get('em'),
                'Mrss\Entity\System'
            )
        );
        $form->bind($system);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->getSystemModel()->save($system);
                $this->getServiceLocator()->get('em')->flush();

                $noun = ucwords($this->getSystemLabel());
                $this->flashMessenger()->addSuccessMessage("$noun saved.");
                return $this->redirect()->toRoute('systems');
            }

        }

        return array(
            'form' => $form
        );
    }

    protected function getSystemLabel()
    {
        return $this->getServiceLocator()->get('Study')->system_label;
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    protected function getSubscriptionModel()
    {
        return $this->getServiceLocator()->get('model.subscription');
    }

    public function addcollegeAction()
    {
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $years = $this->getSubscriptionModel()->getYearsWithSubscriptions($this->currentStudy());
        $form = new \Mrss\Form\SystemCollege($collegeModel, $years);

        $systemId = $this->params('system_id');
        $system = $this->getSystemModel()->find($systemId);

        $collegeId = $this->params()->fromRoute('college_id');
        if ($collegeId) {
            //$college = $this->
            $form->get('college_id')->setValue($collegeId);

            $college = $collegeModel->find($collegeId);
            $yearsEnabled = $this->getYearsWithMembership($system, $college);

            $form->get('years')->setValue($yearsEnabled);
        }


        //$year = $this->currentStudy()->getCurrentYear();

        if (empty($system)) {
            throw new \Exception('System not found');
        }

        // Process the form
        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();

                // Find the college
                $collegeId = $data['college_id'];
                $college = $collegeModel->find($collegeId);

                if (empty($college)) {
                    throw new \Exception('Invalid college chosen.');
                }


                foreach ($years as $year) {
                    $enabled = in_array($year, $data['years']);


                    // See if the membership exists
                    $membership = $this->getSystemMembershipModel()->findBySystemCollegeYear($system, $college, $year);

                    if (!$membership && $enabled) {
                        // Add it
                        $membership = new SystemMembership();
                        $membership->setSystem($system);
                        $membership->setCollege($college);
                        $membership->setYear($year);
                        $membership->setDataVisibility('public');

                        $this->getSystemMembershipModel()->save($membership);
                    } elseif ($membership && !$enabled) {
                        // Remove it
                        $this->getSystemMembershipModel()->delete($membership);
                    }
                }

                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Institution added.');
                return $this->redirect()
                    ->toRoute('systems/view', array('id' => $system->getId()));
            }

        }

        return array(
            'form' => $form,
            'system' => $system
        );
    }

    protected function getMemberships($system, $college)
    {
        $years = $this->getSubscriptionModel()->getYearsWithSubscriptions($this->currentStudy());

        $memberships = array();
        foreach ($years as $year) {
            // See if the membership exists
            $membership = $this->getSystemMembershipModel()->findBySystemCollegeYear($system, $college, $year);

            if ($membership) {
                $memberships[$year] = $membership;
            }
        }

        return $memberships;
    }

    protected function getYearsWithMembership($system, $college)
    {
        $memberships = $this->getMemberships($system, $college);

        return array_keys($memberships);
    }

    /**
     * Remove a college from the system
     */
    public function removecollegeAction()
    {
        $collegeId = $this->params('college_id');
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $college = $collegeModel->find($collegeId);

        if (empty($college)) {
            throw new \Exception('College not found');
        }

        // What system were they in (for redirection)?
        $system = $college->getSystem();

        // Remove the system association
        $college->setSystem(null);
        $collegeModel->save($college);
        $this->getServiceLocator()->get('em')->flush();

        $this->flashMessenger()
            ->addSuccessMessage('Institution removed from system.');
        return $this->redirect()
            ->toRoute('systems/view', array('id' => $system->getId()));
    }

    /**
     * @return \Mrss\Model\User
     */
    protected function getUserModel()
    {
        return $this->getServiceLocator()->get('model.user');
    }

    /**
     * Show a list of all users attached to colleges in the system and promote
     * the selected user to system_admin
     */
    public function addadminAction()
    {
        $systemId = $this->params('system_id');
        $role = $this->params('role');

        $roleLabel = 'admin';
        if ($role == 'system_viewer') {
            $roleLabel = 'viewer';
        }

        $systemModel = $this->getServiceLocator()->get('model.system');
        $system = $systemModel->find($systemId);

        if (empty($system)) {
            throw new \Exception('System not found');
        }

        $form = new \Mrss\Form\SystemAdmin($system, $role);

        // Process the form, promoting the user
        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();

                // Get the user
                $userModel = $this->getUserModel();
                $user = $userModel->find($data['user_id']);

                if (empty($user)) {
                    throw new \Exception('User not found');
                }

                // Set the role and save
                //$user->setRole($data['role']);

                // New regime for Envisio: leave role, but set systemsAdministered
                $user->addSystemAdministered($systemId);


                $userModel->save($user);
                $this->getServiceLocator()->get('em')->flush();

                // Show a message and redirect
                $noun = ucwords($this->getSystemLabel());
                $this->flashMessenger()->addSuccessMessage("$noun $roleLabel added.");
                return $this->redirect()
                    ->toRoute('systems/view', array('id' => $system->getId()));
            }
        }

        $viewModel = new ViewModel(
            array(
                'form' => $form,
                'roleLabel' => $roleLabel,
                'system' => $system
            )
        );
        //$viewModel->setTerminal(true);

        return $viewModel;
    }

    public function removeadminAction()
    {
        $userId = $this->params('user_id');
        $userModel = $this->getServiceLocator()->get('model.user');
        $user = $userModel->find($userId);

        if (empty($user)) {
            throw new \Exception('User not found');
        }

        // What system were they in (for redirection)?
        $system = $user->getCollege()->getSystem();

        // What should the new role be?
        $oldRole = $user->getRole();
        $newRole = 'data';
        if ($oldRole == 'system_viewer') {
            $newRole = 'viewer';
        }

        // Remove the system admin role
        $user->setRole($newRole);
        $userModel->save($user);
        $this->getServiceLocator()->get('em')->flush();

        $this->flashMessenger()
            ->addSuccessMessage('System admin role removed from user.');
        return $this->redirect()
            ->toRoute('systems/view', array('id' => $system->getId()));

    }

    public function getSystem($id)
    {
        if (!empty($id)) {
            $system = $this->getSystemModel()->find($id);
        }

        if (empty($system)) {
            $system = new System;
        }

        return $system;
    }

    /**
     * @return \Mrss\Model\SystemMembership
     */
    public function getSystemMembershipModel()
    {
        return $this->getServiceLocator()->get('model.system.membership');
    }
}
