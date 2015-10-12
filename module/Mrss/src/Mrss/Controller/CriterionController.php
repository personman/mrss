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

    public function getCriterion($id = null)
    {
        if (empty($id)) {
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
