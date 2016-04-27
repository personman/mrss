<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Entity\Suppression;
use Mrss\Form\Suppression as SForm;

/**
 * Class SubscriptionController
 *
 * @package Mrss\Controller
 */
class SuppressionController extends AbstractActionController
{
    public function editAction()
    {
        $subscriptionId = $this->params('subscription');
        $subscription = $this->getSubscriptionModel()->find($subscriptionId);

        $form = $this->getForm($subscription);

        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();

                // Process the form

                // Clear old suppressions
                $this->clearSuppressions($subscription);

                // Save new ones
                foreach ($data['suppressions'] as $benchmarkGroupId) {
                    $suppression = new Suppression();

                    if ($benchmarkGroup = $this->getBenchmarkGroupModel()->find($benchmarkGroupId)) {
                        $suppression->setBenchmarkGroup($benchmarkGroup);
                        $suppression->setSubscription($subscription);

                        $this->getSuppressionModel()->save($suppression);
                    }
                }

                $this->getSuppressionModel()->getEntityManager()->flush();

                $this->flashMessenger()->addSuccessMessage("Suppressions saved.");
                return $this->redirect()->toRoute(
                    'colleges/view',
                    array(
                        'id' => $subscription->getCollege()->getId())
                );
            }
        }

        return array(
            'form' => $form,
            'college' => $subscription->getCollege()
        );
    }

    /**
     * @param \Mrss\Entity\Subscription $subscription
     * @return SForm
     */
    public function getForm($subscription)
    {
        $study = $this->getStudy();
        $form = new SForm($study, $subscription);

        $selected = array();
        foreach ($subscription->getSuppressions() as $suppression) {
            $selected[] = $suppression->getBenchmarkGroup()->getId();
        }

        $form->get('suppressions')->setValue($selected);

        return $form;
    }

    /**
     * @param \Mrss\Entity\Subscription $subscription
     */
    public function clearSuppressions($subscription)
    {
        foreach ($subscription->getSuppressions() as $suppression) {
            $this->getSuppressionModel()->delete($suppression);
        }

        $this->getSuppressionModel()->getEntityManager()->flush();
    }

    /**
     * @return \Mrss\Model\Subscription
     */
    public function getSubscriptionModel()
    {
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        return $subscriptionModel;
    }

    /**
     * @return \Mrss\Model\BenchmarkGroup
     */
    public function getBenchmarkGroupModel()
    {
        $model = $this->getServiceLocator()->get('model.benchmark.group');
        return $model;
    }

    /**
     * @return \Mrss\Model\Suppression
     */
    public function getSuppressionModel()
    {
        $model = $this->getServiceLocator()->get('model.suppression');
        return $model;
    }

    /**
     * @return \Mrss\Entity\Study
     */
    public function getStudy()
    {
        return $this->currentStudy();
    }
}
