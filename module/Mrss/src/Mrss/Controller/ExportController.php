<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ExportController extends AbstractActionController
{

    public function indexAction()
    {
        /** @var /Mrss/Service/DataExport $exportService */
        $exportService = $this->getServiceLocator()->get('export');

        $studies = array($this->currentStudy()->getId());
        $exportService->getFullDataDump($studies);

        return array(
        );
    }

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
}
