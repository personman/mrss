<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\Study;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\View\Model\ViewModel;
use Mrss\Form\OfferCode as OfferCodeForm;
use Mrss\Entity\OfferCode;

class OfferCodeController extends AbstractActionController
{
    /**
     * @var \Mrss\Model\Study
     */
    protected $studyModel;

    public function indexAction()
    {
        $studyId = $this->params('study');
        $study = $this->getStudyModel()->find($studyId);

        return array(
            'study' => $study
        );
    }

    public function editAction()
    {
        $studyId = $this->params('study');
        if (empty($studyId) && $this->getRequest()->isPost()) {
            $studyId = $this->params()->fromPost('study');
        }

        $study = $this->getStudyModel()->find($studyId);

        $id = $this->params('id');
        if (empty($id) && $this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id');
        }

        $offerCode = $this->getOfferCode($id);

        $form = new OfferCodeForm();

        $form->setHydrator(
            new DoctrineHydrator(
                $this->getServiceLocator()->get('em'),
                'Mrss\Entity\OfferCode'
            )
        );
        $form->bind($offerCode);
        $form->get('study')->setValue($studyId);

        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->getOfferCodeModel()->save($offerCode);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Offer code saved.');
                return $this->redirect()->toRoute(
                    'offercodes',
                    array('study' => $studyId)
                );
            }

        }

        return array(
            'study' => $study,
            'form' => $form
        );
    }

    public function deleteAction()
    {
        $studyId = $this->params('study');
        $id = $this->params('id');

        $offerCode = $this->getOfferCode($id);

        if (!empty($offerCode)) {
            $this->getOfferCodeModel()->delete($offerCode);
        }

        $this->flashMessenger()->addSuccessMessage("Offer code deleted.");
        return $this->redirect()->toRoute(
            'offercodes',
            array('study' => $studyId)
        );
    }

    public function setStudyModel($studyModel)
    {
        $this->studyModel = $studyModel;

        return $this;
    }

    public function getStudyModel()
    {

        if (empty($this->studyModel)) {
            $this->studyModel = $this->getServiceLocator()->get('model.study');
        }

        return $this->studyModel;
    }

    public function getOfferCode($id)
    {
        if (!empty($id)) {
            $offerCode = $this->getOfferCodeModel()->find($id);
        }

        if (empty($offerCode)) {
            $offerCode = new OfferCode;
        }

        return $offerCode;
    }

    public function getOfferCodeModel()
    {
        return $this->getServiceLocator()->get('model.offerCode');
    }
}
