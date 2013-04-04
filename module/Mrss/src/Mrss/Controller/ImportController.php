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
        // Load the importer from the service manager
        $sm = $this->getServiceLocator();
        $importer = $sm->get('import.nccbp');

        // Import the colleges from nccbp
        $importer->importColleges();
        $stats = $importer->getStats();

        // Redirect
        $message = "College import complete. Imported: $stats[imported],
        skipped: $stats[skipped].";
        $this->flashMessenger()->addSuccessMessage($message);
        $this->redirect()->toUrl('/colleges');
    }

    public function obsAction()
    {
        $importer = $this->getServiceLocator()->get('import.nccbp');

        $importer->importObservations();
        die('done');
    }
}
