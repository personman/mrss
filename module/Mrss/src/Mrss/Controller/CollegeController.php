<?php

namespace Mrss\Controller;

use Mrss\Form\AbstractForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;
use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class CollegeController extends AbstractActionController
{

    public function indexAction()
    {
        $Colleges = $this->getServiceLocator()->get('model.college');

        return array(
            'colleges' => $Colleges->findAll()
        );
    }

    public function flashtestAction()
    {
        $this->flashMessenger()->addMessage('Regular message.');
        $this->flashMessenger()->addSuccessMessage('Success!');
        $this->flashMessenger()->addErrorMessage('Error!');

        return $this->redirect()->toUrl('/colleges');
    }

    public function viewAction()
    {
        $Colleges = $this->getServiceLocator()->get('model.college');
        $college = $Colleges->find($this->params('id'));

        $Studies = $this->getServiceLocator()->get('model.study');

        // Handle invalid id
        if (empty($college)) {
            $this->flashMessenger()->addErrorMessage("Invalide college id.");
            return $this->redirect()->toUrl('/colleges');
        }

        return array(
            'college' => $college,
            'study' => $Studies->find(1)
        );
    }

    /**
     * This is very slow. Need to store the lat/lng of each college instead of
     * letting the map script look it up by address.
     *
     * @return array
     */
    public function mapAction()
    {
        $Colleges = $this->getServiceLocator()->get('model.college');

        return array('colleges' => $Colleges->findAll());
    }

    public function peersAction()
    {
        // Get a list of subscriptions to the current study for all years
        /** @var \Mrss\Model\College $collegeModel */
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $studyId = $this->currentStudy()->getId();

        $colleges = $collegeModel->findByStudy($this->currentStudy());

        // Map markers
        $markers = array();
        foreach ($colleges as $college) {
            $lat = $college->getLatitude();
            $lon = $college->getLongitude();

            if ($lat && $lon) {
                $markers[] = array(
                    'latLng' => array($lat, $lon),
                    'name' => $college->getName()
                );
            }
        }
        $markers = json_encode($markers);

        return array(
            'colleges' => $colleges,
            'markers' => $markers
        );
    }

    public function editAction()
    {
        /** @var \Mrss\Model\College $collegeModel */
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $form = new AbstractForm('college');

        $collegeFieldset = new \Mrss\Form\Fieldset\College(true);

        $collegeFieldset->setUseAsBaseFieldset(true);

        $collegeId = $this->params()->fromRoute('id');
        if (empty($collegeId)) {
            if ($institution = $this->params()->fromPost('institution')) {
                $collegeId = $institution['id'];
            }
        }

        if (empty($collegeId)) {
            /** @var \Mrss\Entity\College $college */
            $college = $this->currentCollege();
            $isAdmin = false;
        } else {
            $college = $collegeModel->find($collegeId);
            $isAdmin = true;
        }

        $redirect = $this->params()->fromRoute('redirect');

        $em = $this->getServiceLocator()->get('em');
        $collegeFieldset->setHydrator(
            new DoctrineHydrator($em, 'Mrss\Entity\College')
        );


        $form->add($collegeFieldset);
        $form->bind($college);
        $form->add($form->getButtonFieldset());

        // Redirect to renew if needed
        $form->addRedirect($redirect);

        // Process the form
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $collegeModel->save($college);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Institution saved.');

                // Get the redirect
                $data = $this->params()->fromPost();

                if (!empty($isAdmin)) {
                    $redirect = $this->redirect()
                        ->toUrl('/colleges/view/' . $college->getId());
                } elseif (!empty($data['redirect'])) {
                    $redirect = $this->redirect()->toUrl('/' . $data['redirect']);
                } else {
                    $redirect = $this->redirect()->toUrl('/members');
                }



                return $redirect;
            }

        }


        return array(
            'form' => $form,
            'isAdmin' => $isAdmin
        );
    }

    public function usersAction()
    {
        $college = $this->currentCollege();

        return array(
            'college' => $college,
            'redirect' => $this->params()->fromRoute('redirect')
        );
    }
}
