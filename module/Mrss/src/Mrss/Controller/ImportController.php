<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;

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
        $stats = $importer->getStats();

        // Redirect
        $message = "Observation import complete. Imported: $stats[imported],
        skipped: $stats[skipped], Elapsed time: $stats[elapsed] seconds.";
        $this->flashMessenger()->addSuccessMessage($message);
        $this->redirect()->toUrl('/colleges');
    }

    public function metaAction()
    {
        $importer = $this->getServiceLocator()->get('import.nccbp');

        $importer->importFieldMetadata();

        $stats = $importer->getStats();
        $message = "Benchmark metadata import complete. Imported:
            $stats[imported], skipped: $stats[skipped].";
        $this->flashMessenger()->addSuccessMessage($message);
        $this->redirect()->toUrl('/colleges');
    }
}
