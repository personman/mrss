<?php

namespace Mrss\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Mrss\Form\Criterion;
use Mrss\Entity\Criterion as CriterionEntity;
use Zend\Mvc\Controller\AbstractActionController;

class CriterionController extends AbstractActionController
{
    public function indexAction()
    {
        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $criteria = $study->getCriteria();

        return array(
            'criteria' => $criteria
        );
    }

    public function addAction()
    {
        $form = $this->getForm();

        $criterion = $this->getCriterion();
        $form->bind($criterion);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->getCriterionModel()->save($criterion);
                $this->getCriterionModel()->getEntityManager()->flush();

                $this->flashMessenger()->addSuccessMessage('Criterion saved.');
                return $this->redirect()->toRoute('criteria');
            }
        }

        return array(
            'form' => $form,
        );
    }

    public function reorderAction()
    {
        $criteriaSequence = $this->params()->fromPost('criteria');

        foreach ($criteriaSequence as $sequence => $criteriaId) {
            $sequence++;

            $criterion = $this->getCriterionModel()->find($criteriaId);
            $criterion->setSequence($sequence);
            $this->getCriterionModel()->save($criterion);
        }

        $this->getCriterionModel()->getEntityManager()->flush();

        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent('ok');
        return $response;
    }

    public function deleteAction()
    {
        $criterionId = $this->params()->fromRoute('id');

        $criterion = $this->getCriterionModel()->find($criterionId);
        $this->getCriterionModel()->delete($criterion);

        $this->flashMessenger()->addSuccessMessage('Criterion deleted.');
        return $this->redirect()->toRoute('criteria');
    }

    protected function getForm()
    {
        $benchmarks = $this->currentStudy()->getStructuredBenchmarks(false, 'id');

        $form = new Criterion($benchmarks);
        $form->setHydrator(
            new DoctrineHydrator(
                $this->getServiceLocator()->get('em'),
                'Mrss\Entity\Criterion'
            )
        );

        return $form;
    }

    public function getCriterion($identifier = null)
    {
        if (empty($identifier)) {
            $criterion = new CriterionEntity();
            $criterion->setStudy($this->currentStudy());
        } else {
        }

        return $criterion;
    }

    /**
     * @return \Mrss\Model\Criterion
     */
    protected function getCriterionModel()
    {
        return $this->getServiceLocator()->get('model.criterion');
    }
}
