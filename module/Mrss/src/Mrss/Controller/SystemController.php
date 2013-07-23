<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\System as SystemForm;
use Mrss\Entity\System;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class SystemController extends AbstractActionController
{
    /**
     * Show the current systems
     *
     * @return array|void
     */
    public function indexAction()
    {
        $systemModel = $this->getServiceLocator()->get('model.system');

        return array(
            'systems' => $systemModel->findAll()
        );
    }

    public function viewAction()
    {
        $systemModel = $this->getServiceLocator()->get('model.system');
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $id = $this->params('id');

        if (empty($id)) {
            throw new \Exception('You cannot view a system without the id.');
        }

        $system = $systemModel->find($id);

        if (empty($system)) {
            throw new \Exception('System not found.');
        }

        return array(
            'system' => $system,
            'colleges' => $collegeModel->findAll()
        );
    }

    public function addAction()
    {
        $form = new SystemForm;

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

                $this->flashMessenger()->addSuccessMessage('System saved.');
                return $this->redirect()->toRoute('systems');
            }

        }

        return array(
            'form' => $form
        );
    }

    public function addcollegeAction()
    {
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $form = new \Mrss\Form\SystemCollege($collegeModel);

        $systemId = $this->params('system_id');
        $system = $this->getSystemModel()->find($systemId);

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

                // Actually write the association
                $college->setSystem($system);
                $collegeModel->save($college);
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

    public function getSystemModel()
    {
        return $this->getServiceLocator()->get('model.system');
    }
}
