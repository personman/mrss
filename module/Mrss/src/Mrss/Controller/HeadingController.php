<?php

namespace Mrss\Controller;

use Mrss\Entity\BenchmarkHeading as HeadingEntity;
use Mrss\Model\Observation as ObservationModel;
use Mrss\Form\BenchmarkHeading as HeadingForm;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class HeadingController extends AbstractActionController
{
    public function editAction()
    {
        $id = $this->params()->fromRoute('id');
        $benchmarkGroup = $this->params()->fromRoute('benchmarkGroup');

        $form = new HeadingForm;

        if (!empty($id) && $id != 'add') {
            $heading = $this->getModel()->find($id);

            if (!$heading) {
                throw new \Exception('Heading not found for id: ' . $id);
            }
        } else {
            $heading = new HeadingEntity();
        }

        // Set type
        $user = $this->zfcUserAuthentication()->getIdentity();
        $type = $user->getAdminBenchmarkSorting();
        $heading->setType($type);

        $heading->setBenchmarkGroup($this->getBenchmarkGroupModel()->find($benchmarkGroup));

        $em = $this->getServiceLocator()->get('em');
        $form->setHydrator(new DoctrineHydrator($em, 'Mrss\Entity\Benchmark'));
        $form->bind($heading);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $data = $this->params()->fromPost();

            $form->setData($data);

            if ($form->isValid()) {
                // Delete?
                $buttons = $this->params()->fromPost('buttons');

                if (!empty($buttons['delete'])) {
                    $this->getModel()->delete($heading);
                    $this->getServiceLocator()->get('em')->flush();

                    $this->flashMessenger()->addSuccessMessage('Heading deleted.');

                    return $this->redirect()->toRoute('benchmark');
                }

                // Save
                $this->getModel()->save($heading);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Heading saved.');
                return $this->redirect()->toRoute('benchmark');
            }
        }


        return array(
            'form' => $form,
            'id' => $id,
            'benchmarkGroup' => $benchmarkGroup
        );
    }

    /**
     * @return \Mrss\Model\BenchmarkHeading
     */
    protected function getModel()
    {
        return $this->getServiceLocator()->get('model.benchmarkHeading');
    }

    /**
     * @return \Mrss\Model\BenchmarkGroup
     */
    protected function getBenchmarkGroupModel()
    {
        return $this->getServiceLocator()->get('model.benchmarkGroup');
    }
}
