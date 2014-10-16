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
        $form = new AbstractForm('college');

        $collegeFieldset = new \Mrss\Form\Fieldset\College;
        $collegeFieldset->setUseAsBaseFieldset(true);

        $college = $this->currentCollege();

        $em = $this->getServiceLocator()->get('em');
        $collegeFieldset->setHydrator(
            new DoctrineHydrator($em, 'Mrss\Entity\College')
        );

        $form->add($collegeFieldset);
        $form->bind($college);
        $form->add($form->getButtonFieldset());

        // Process the form
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $collegeModel = $this->getServiceLocator()->get('model.college');
                $collegeModel->save($college);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Institution saved.');
                return $this->redirect()->toUrl('/members');
            }

        }


        return array(
            'form' => $form
        );
    }

    public function usersAction()
    {
        $college = $this->currentCollege();

        return array(
            'college' => $college
        );
    }
}
