<?php

namespace Mrss\Controller;

use Mrss\Form\PeerCollege;
use Mrss\Form\PeerGroup as PeerGroupForm;
use Mrss\Entity\PeerGroup;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Mrss\Form\PeerComparisonDemographics;

class PeerGroupController extends ReportController
{
    public function indexAction()
    {
        $peerGroups = $this->getPeerGroupModel()->findByUserAndStudy(
            $this->getCurrentUser(),
            $this->currentStudy()
        );

        $collegeNames = array();
        foreach ($peerGroups as $peerGroup) {
            $colleges = $this->getCollegeModel()->findByIds($peerGroup->getPeers());

            $names = array();
            foreach ($colleges as $college) {
                $names[] = $college->getName() . ' (' . $college->getState() . ')';
            }

            $collegeNames[$peerGroup->getId()] = $names;
        }

        return array(
            'peerGroups' => $peerGroups,
            'collegeNames' => $collegeNames
        );
    }

    public function addAction()
    {
        $form = $this->getForm();

        $peerGroup = $this->getPeerGroup();
        $form->bind($peerGroup);


        if ($this->getRequest()->isPost()) {
            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->getPeerGroupModel()->save($peerGroup);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Peer group saved.');

                return $this->redirect()->toRoute(
                    'peer-groups'
                );
            }
        }

        return array(
            'form' => $form
        );
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id');

        if (empty($id)) {
            throw new \Exception('Peer group id missing.');
        }

        $peerGroup = $this->getPeerGroup($id);
        $form = $this->getForm();
        $form->bind($peerGroup);

        // Process form submission, if any
        if ($this->getRequest()->isPost()) {
            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->getPeerGroupModel()->save($peerGroup);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Peer group saved.');

                return $this->redirect()->toRoute(
                    'peer-groups'
                );
            }
        }


        // Get the member colleges so they can remove them as needed
        $colleges = $this->getCollegeModel()->findByIds($peerGroup->getPeers());

        return array(
            'peerGroup' => $peerGroup,
            'form' => $form,
            'colleges' => $colleges
        );
    }

    public function deleteAction()
    {
        $groupId = $this->params()->fromRoute('id');

        $peerGroup = $this->getPeerGroupModel()->find($groupId);

        $this->checkPeerGroupOwnership($peerGroup);

        if ($peerGroup) {
            $this->getPeerGroupModel()->delete($peerGroup);
        }

        $this->flashMessenger()->addSuccessMessage('Peer group deleted.');
        return $this->redirect()->toRoute('peer-groups');
    }

    /**
     * @return \Mrss\Model\College
     */
    protected function getCollegeModel()
    {
        return $this->getServiceLocator()->get('model.college');
    }

    /**
     * @return \Mrss\Model\PeerGroup
     */
    public function getPeerGroupModel()
    {
        return $this->getServiceLocator()->get('model.peer.group');
    }

    public function getPeerGroup($id = null)
    {
        if (empty($id)) {
            $peerGroup = new PeerGroup();
            //$peerGroup->setCollege($this->currentCollege());
            $peerGroup->setUser($this->getCurrentUser());
            $peerGroup->setStudy($this->currentStudy());
            $peerGroup->setYear($this->currentStudy()->getCurrentYear());
        } else {
            $peerGroup = $this->getPeerGroupModel()->find($id);

            $this->checkPeerGroupOwnership($peerGroup);
        }

        return $peerGroup;
    }

    protected function checkPeerGroupOwnership($peerGroup)
    {
        // Make sure it belongs to the logged-in user
        if ($this->getCurrentUser()->getId() != $peerGroup->getUser()->getId()) {
            $userId = $this->getCurrentUser()->getId();
            $peerId = $peerGroup->getId();
            throw new \Exception(
                "User $userId tried editing peer group $peerId, but it does not belong to them."
            );
        }
    }

    public function getForm()
    {
        $form = new PeerGroupForm;

        $form->setHydrator(
            new DoctrineHydrator(
                $this->getServiceLocator()->get('em'),
                'Mrss\Entity\PeerGroup'
            )
        );

        return $form;
    }

    public function deletePeerAction()
    {
        $id = $this->params()->fromRoute('id');
        $peer = $this->params()->fromRoute('peer');

        $peerGroup = $this->getPeerGroup($id);
        $peerGroup->removePeer($peer);

        $this->getPeerGroupModel()->save($peerGroup);
        $this->getPeerGroupModel()->getEntityManager()->flush();

        $this->flashMessenger()->addSuccessMessage('Peer removed from group.');
        return $this->redirect()->toRoute('peer-groups/edit', array('id' => $peerGroup->getId()));
    }

    public function addPeerAction()
    {
        $colleges = $this->getCollegeModel()->findAll();

        $form = new PeerCollege($colleges);

        $id = $this->params()->fromRoute('id');
        $peerGroup = $this->getPeerGroup($id);

        // Process form submission, if any
        if ($this->getRequest()->isPost()) {
            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $collegeId = $data['college'];
                $peerGroup->addPeer($collegeId);

                $this->getPeerGroupModel()->save($peerGroup);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Peer added.');

                return $this->redirect()->toRoute(
                    'peer-groups/edit',
                    array('id' => $id)
                );
            }
        }


        return array(
            'form' => $form
        );
    }


    public function addDemographicAction()
    {
        $form = new PeerComparisonDemographics($this->currentStudy());

        $id = $this->params()->fromRoute('id');
        $peerGroup = $this->getPeerGroup($id);

        // Bind an empty peer group just to serve as the container for these criteria
        /*$emptyPeerGroup = new PeerGroup();
        $emptyPeerGroup->setCollege($this->currentCollege());
        $emptyPeerGroup->setYear($this->currentStudy()->getCurrentYear());
        $em = $this->getServiceLocator()->get('em');
        $form->setHydrator(new DoctrineHydrator($em, 'Mrss\Entity\PeerGroup'));
        $form->bind($emptyPeerGroup);*/


        if ($this->getRequest()->isPost()) {
            $postData = $this->params()->fromPost();
            unset($postData['buttons']);

            // Handle empty multiselects
            /*$multiselects = array(
                'states',
                'environments',
                'facultyUnionized',
                'staffUnionized',
                'institutionalType',
                'institutionalControl',
                'onCampusHousing',
                'fourYearDegrees'
            );

            foreach ($multiselects as $multiselect) {
                if (empty($postData[$multiselect])) {
                    $postData[$multiselect] = array();
                }
            }*/

            $form->setData($postData);

            if ($form->isValid()) {
                //$this->getPeerGroupModel()->save($peerGroup);

                /** @var \Mrss\Model\College $collegeModel */
                $collegeModel = $this->getServiceLocator()->get('model.college');

                /*$colleges = $collegeModel->findByPeerGroup(
                    $emptyPeerGroup,
                    $this->currentStudy()
                );*/

                $year = $this->currentStudy()->getLatestReportYear();

                $colleges = $collegeModel->findByCriteria(
                    $postData,
                    $this->currentStudy(),
                    $this->currentCollege(),
                    $year
                );

                $names = array();
                foreach ($colleges as $college) {
                    $peerGroup->addPeer($college->getId());
                    $names[] = $college->getName() . ' (' . $college->getState() . ')';
                }

                $count = count($colleges);

                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage($count . ' peers added: ' . implode(', ', $names));

                return $this->redirect()->toRoute(
                    'peer-groups/edit',
                    array('id' => $id)
                );
            }
        }

        return array(
            'form' => $form
        );
    }

    /**
     * @return \Mrss\Entity\User
     */
    public function getCurrentUser()
    {
        $currentUser = $this->zfcUserAuthentication()->getIdentity();

        return $currentUser;
    }
}
