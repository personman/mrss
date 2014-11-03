<?php


namespace Mrss\Controller;

use Behat\Gherkin\Exception\Exception;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Debug\Debug;
use Zend\Session\Container;
use Zend\Json\Json;
use Zend\Console\Request as ConsoleRequest;

class ImportController extends AbstractActionController
{
    protected $triggerInBackground = false;

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
     * This action should be hit by an ajax call. It just kicks off the background
     * import process.
     */
    public function triggerAction()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(3600);

        $type = $this->params()->fromQuery('type');
        $year = $this->params()->fromQuery('year');

        $imports = $this->getImports(true);

        if (empty($imports[$type])) {
            throw new \Exception('Invalid import type.');
        }

        // Trigger the importer in the background
        if ($this->triggerInBackground) {
            $config = $this->getServiceLocator()->get('config');
            if (!empty($config['php_path'])) {
                $php = $config['php_path'];
            } else {
                $php = "php";
            }
            $output = array();
            $command = "nohup nice -n 10 $php public/index.php import $type $year > /dev/null &";
            exec($command, $output);
            //pr($command);
            //pr($output);
        } else {
            $this->backgroundAction($type, $year);
        }

        return new JsonModel(array('status' => 'ok'));
    }

    /**
     * Triggered by the console, this actually runs the import.
     *
     * @throws \Exception
     */
    public function backgroundAction($type = null, $year = null)
    {
        $this->disableQueryLogging();

        //phpinfo(); die;
        if (!$this->getRequest() instanceof ConsoleRequest) {
            //throw new \Exception('Console requests only.');
        }

        if (is_null($type)) {
            $type = $this->params('type');
        }

        if (is_null($type)) {
            $type = $this->params()->fromQuery('type');
        }

        if (is_null($year)) {
            $year = $this->params('year');
        }

        $imports = $this->getImports(true);

        if (empty($imports[$type])) {
            throw new \Exception("'$type' is an invalid import type.");
        }
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        $import = $imports[$type];

        // Load the importer from the service manager
        $sm = $this->getServiceLocator();
        $importer = $sm->get('import.nccbp');

        // Import
        $method = $import['method'];
        if (!method_exists($importer, $method)) {
            throw new \Exception("Method $method does not exist for importer.");
        }

        try {
            // This is the actual import
            if (!empty($import['argument'])) {
                if (!empty($import['year'])) {
                    $importer->$method($import['argument'], $year);
                } else {
                    $importer->$method($import['argument']);
                }
            } else {
                if (!empty($import['year'])) {
                    $importer->$method($year);
                } else {
                    $importer->$method();
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return new JsonModel();
    }

    public function progressAction()
    {
        $type = $this->params()->fromQuery('type');
        $sm = $this->getServiceLocator();
        $importer = $sm->get('import.nccbp');

        $stats = $importer->getProgress($type);

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
        return $this->redirect()->toUrl('/colleges');
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
        return $this->redirect()->toUrl('/colleges');
    }

    public function metaAction()
    {
        $importer = $this->getServiceLocator()->get('import.nccbp');

        $importer->importFieldMetadata();

        $stats = $importer->getStats();
        $message = "Benchmark metadata import complete. Imported:
            $stats[imported], skipped: $stats[skipped].";
        $this->flashMessenger()->addSuccessMessage($message);
        return $this->redirect()->toUrl('/colleges');
    }

    public function benchmarkgroupsAction()
    {
        $importer = $this->getServiceLocator()->get('import.nccbp');

        $importer->importBenchmarkGroups();

        $stats = $importer->getStats();
        $message = "Benchmark groups import complete. Imported:
            $stats[imported], skipped: $stats[skipped].";
        $this->flashMessenger()->addSuccessMessage($message);
        return $this->redirect()->toUrl('/colleges');
    }

    protected function getImports($all = false)
    {
        return $this->getServiceLocator()->get('import.nccbp')->getImports($all);
    }

    public function disableQueryLogging()
    {
        // Turn off query logging
        $this->getServiceLocator()
            ->get('em')
            ->getConnection()
            ->getConfiguration()
            ->setSQLLogger(null);
    }

    public function convertjsonAction()
    {

        $result = null;

        $form = new \Zend\Form\Form();
        $form->add(
            array(
                'type' => 'textarea',
                'name' => 'json',
                'attributes' => array(
                    'rows' => 20
                )
            )
        );

        $form->add(
            array(
                'type' => 'submit',
                'attributes' => array(
                    'value' => 'Convert',
                ),
                'name' => 'convert'
            )
        );

        $form->setAttribute('method', 'post');

        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $data = $data['json'];
                $result = json_decode($data, true);
                $result = var_export($result, true);

                $result = preg_replace('/(\d+) \=\> /', '', $result);
                $result = preg_replace('/array \(/', 'array(', $result);
                $result = preg_replace('/\>(\s+)array/s', '> array', $result);

            }
        }


        return array(
            'result' => $result,
            'form' => $form
        );
    }
}
