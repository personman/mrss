<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Form\Section as SectionForm;

class SectionController extends AbstractActionController
{
    /**
     * @var \Mrss\Model\Study
     */
    protected $studyModel;

    public function indexAction()
    {
        $studyId = $this->params()->fromRoute('study');
        $study = $this->getStudyModel()->find($studyId);

        return array(
            'study' => $study
        );
    }

    protected function getStudyModel()
    {
        return $this->getServiceLocator()->get('model.study');
    }

    public function editAction()
    {
        $studyId = $this->params()->fromRoute('study');
        $study = $this->getStudyModel()->find($studyId);


        $id = $this->params('id');
        if (empty($id) && $this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id');
        }

        $form = new SectionForm;

        /*$form->setHydrator(
            new DoctrineHydrator(
                $this->getServiceLocator()->get('em'),
                'Mrss\Entity\Study'
            )
        );
        $form->bind($study);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                //var_dump($this->params()->fromPost());
                //var_dump($study); die;
                $this->getStudyModel()->save($study);

                $this->flashMessenger()->addSuccessMessage('Study saved.');
                return $this->redirect()->toRoute('studies');
            }

        }
        */

        return array(
            'form' => $form,
            'study' => $study,
            'id' => $id
        );
    }
}
