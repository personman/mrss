<?php


namespace Mrss\Controller;

use Behat\Gherkin\Exception\Exception;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Debug\Debug;
use Zend\Session\Container;
use Zend\Json\Json;

class ImportController extends AbstractActionController
{

    /**
     * Display controls and progress bars for managing all import types
     *
     * @return array
     */
    public function indexAction()
    {

        return array(
            'imports' => $this->getImports()
        );
    }

    /**
     * This action should be hit by an ajax call. It just kicks off the backgroun
     * import process.
     */
    public function triggerAction()
    {
        $type = $this->params()->fromQuery('type');

        $imports = $this->getImports();

        if (empty($imports[$type])) {
            throw new \Exception('Invalid import type.');
        }

        // Trigger the importer in the background
        if (true) {
            shell_exec("nohup php public/index.php import $type > /dev/null");
        } else {
            $this->backgroundAction($type);
        }

        return new JsonModel(array('status' => 'ok'));
    }

    /**
     * Triggered by the console, this actually runs the import.
     *
     * @throws \Exception
     */
    public function backgroundAction($type = null)
    {
        if (is_null($type)) {
            $type = $this->params('type');
        }

        $imports = $this->getImports();

        if (empty($imports[$type])) {
            throw new \Exception('Invalid import type.');
        }

        $import = $imports[$type];

        echo "Starting import of $import[label]...\n";

        // Load the importer from the service manager
        $sm = $this->getServiceLocator();
        $importer = $sm->get('import.nccbp');

        // Import
        $method = $import['method'];
        if (!method_exists($importer, $method)) {
            throw new \Exception("Method $method does not exist for importer.");
        }

        // This is the actual import
        $importer->$method();

        $stats = $importer->getStats();
        $message = "Import complete. Imported: $stats[imported],
        skipped: $stats[skipped].\n";
        echo $message;
    }

    public function progressAction()
    {
        $sm = $this->getServiceLocator();
        $importer = $sm->get('import.nccbp');

        $stats = $importer->getProgress();

        // Return json with no layout
        return new JsonModel($stats);
    }

    public function collegesAction()
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

        $importer->importAllObservations();
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

    protected function getImports()
    {
        return array(
            'colleges' => array(
                'label' => 'Colleges',
                'method' => 'importColleges'
            ),
            'benchmarks' => array(
                'label' => 'Benchmarks',
                'method' => 'importFieldMetadata'
            ),
            'observations' => array(
                'label' => 'Observations',
                'method' => 'importAllObservations'
            )
        );

    }
}
