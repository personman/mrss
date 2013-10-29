<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ExportController extends AbstractActionController
{

    public function indexAction()
    {
        /** @var /Mrss/Service/DataExport $exportService */
        $exportService = $this->getServiceLocator()->get('export');

        $exportService->getFullDataDump(array(1));

        return array(
        );
    }
}
