<?php

namespace Mrss\Controller;

use Mrss\Entity\Section;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
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

    protected function getSectionModel()
    {
        return $this->getServiceLocator()->get('model.section');
    }

    public function editAction()
    {
        $studyId = $this->params()->fromRoute('study');
        $study = $this->getStudyModel()->find($studyId);


        $section = $this->getSection();

        $form = new SectionForm;

        $form->setHydrator(
            new DoctrineHydrator(
                $this->getServiceLocator()->get('em'),
                'Mrss\Entity\Section'
            )
        );
        $form->bind($section);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->getSectionModel()->save($section);

                $this->flashMessenger()->addSuccessMessage('Module saved.');
                return $this->redirect()->toRoute('sections', array('study' => $study->getId()));
            }

        }

        return array(
            'form' => $form,
            'study' => $study,
            'section' => $section
        );
    }

    protected function getSection()
    {

        $sectionId = $this->params('id');
        if (empty($sectionId) && $this->getRequest()->isPost()) {
            $sectionId = $this->params()->fromPost('id');
        }

        $section = null;
        if ($sectionId) {
            $section = $this->getSectionModel()->find($sectionId);
        }

        if (empty($section)) {
            $section = new Section();
        }

        return $section;
    }

}
