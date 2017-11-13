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
        $study = $this->getStudy();

        $sections = $this->getSectionModel()->findByStudy($study->getId());

        return array(
            'study' => $study,
            'sections' => $sections
        );
    }

    protected function getStudyModel()
    {
        return $this->getServiceLocator()->get('model.study');
    }

    /**
     * @return \Mrss\Model\Section
     */
    protected function getSectionModel()
    {
        return $this->getServiceLocator()->get('model.section');
    }

    public function editAction()
    {
        $study = $this->getStudy();
        $section = $this->getSection();

        $entityManager = $this->getServiceLocator()->get('em');
        $form = new SectionForm($entityManager);


        $form->setHydrator(
            new DoctrineHydrator(
                $entityManager,
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
            $section->setStudy($this->getStudy());
        }

        return $section;
    }

    protected function getStudy()
    {
        $studyId = $this->params()->fromRoute('study');
        $study = $this->getStudyModel()->find($studyId);

        return $study;
    }
}
