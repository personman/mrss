<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ExportController extends AbstractActionController
{

    public function indexAction()
    {
        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $years = $subscriptionModel->getYearsWithSubscriptions($this->currentStudy());

        return array(
            'years' => $years
        );
    }

    /**
     * Export all data from the given studies
     */
    public function fullAction()
    {
        $year = $this->params()->fromRoute('year');

        /** @var \Mrss\Service\DataExport $exportService */
        $exportService = $this->getServiceLocator()->get('export');

        $studies = array($this->currentStudy()->getId());
        $exportService->getFullDataDump($studies, $year);

        return array(
        );
    }

    /**
     * This route is not set up yet @todo
     *
     * @return array
     */
    public function nccbpAction()
    {
        /** @var \Mrss\Service\DataExport $exportService */
        //$exportService = $this->getServiceLocator()->get('export');

        //$exportService->getFullDataDump(array(1));


        $exportService = $this->getServiceLocator()->get('export.nccbp');
        $exportService->export();

        return array(
        );
    }

    public function usersAction()
    {
        /** @var \Mrss\Service\UserExport $exportService */
        $exportService = $this->getServiceLocator()->get('export.users');

        $year = $this->params()->fromRoute('year', null);

        $exportService->setStudy($this->currentStudy());
        $exportService->export($year);

        die;
    }
}
