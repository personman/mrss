<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ExportController extends AbstractActionController
{

    public function indexAction()
    {

    }

    /**
     * Export all data from the given studies
     */
    public function fullAction()
    {
        /** @var /Mrss/Service/DataExport $exportService */
        $exportService = $this->getServiceLocator()->get('export');

        $studies = array($this->currentStudy()->getId());
        $exportService->getFullDataDump($studies);

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
        /** @var /Mrss/Service/DataExport $exportService */
        //$exportService = $this->getServiceLocator()->get('export');

        //$exportService->getFullDataDump(array(1));


        $exportService = $this->getServiceLocator()->get('export.nccbp');
        $exportService->export();

        return array(
        );
    }

    public function usersAction()
    {
        /** @var /Mrss/Service/UserExport $exportService */
        $exportService = $this->getServiceLocator()->get('export.users');
        $exportService->setStudy($this->currentStudy());
        $exportService->export();

        die;
    }
}
