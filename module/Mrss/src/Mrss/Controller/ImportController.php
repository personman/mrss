<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;
use Mrss\Service\ImportNccbp;

class ImportController extends AbstractActionController
{

    public function indexAction()
    {
        // Should the DI/servicelocator be able to load these dependencies for me?
        $sm = $this->getServiceLocator();
        $nccbpDb = $sm->get('nccbp-db');
        $em = $sm->get('doctrine.entitymanager.orm_default');

        // Run the importer
        $importer = new ImportNccbp($nccbpDb, $em);
        $importer->importColleges();
        $stats = $importer->getStats();

        // Redirect
        $message = "College import complete. Imported: $stats[imported],
        skipped: $stats[skipped].";
        $this->flashMessenger()->addSuccessMessage($message);
        $this->redirect()->toUrl('/colleges');
    }
}
