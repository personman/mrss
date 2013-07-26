<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class ObservationController extends AbstractActionController
{
    protected $systemAdminSessionContainer;

    public function viewAction()
    {
        $observationId = $this->params('id');
        $ObservationModel = $this->getServiceLocator()->get('model.observation');
        $BenchmarkGroupModel = $this->getServiceLocator()
            ->get('model.benchmarkGroup');

        $benchmarkGroupId = $this->params('benchmarkGroupId');
        if (!empty($benchmarkGroupId)) {
            $benchmarkGroup =  $this->getServiceLocator()
                ->get('model.benchmarkGroup')
                ->find($benchmarkGroupId);
        } else {
            $benchmarkGroup = null;
        }

        $observation = $ObservationModel->find($observationId);

        return array(
            'observation' => $observation,
            'benchmarkGroups' => $BenchmarkGroupModel->findAll(),
            'benchmarkGroup' => $benchmarkGroup,
            'fields' => $this->getFields($observation->getYear(), $benchmarkGroup)
        );
    }

    public function editAction()
    {
        $observationId = $this->params('id');
        $benchmarkGroupId = $this->params('benchmarkGroupId');

        $ObservationModel = $this->getServiceLocator()->get('model.observation');
        $observation = $ObservationModel->find($observationId);

        $benchmarkGroup = $this->getServiceLocator()
            ->get('model.benchmarkGroup')
            ->find($benchmarkGroupId);

        $formService = $this->getServiceLocator()
            ->get('service.formBuilder');
        $form = $formService->buildForm($benchmarkGroup, $observation->getYear());

        $form->setAttribute('class', 'form-horizontal');

        // Set up hydrator

        // bind observation to form, which will populate it with values
        $form->bind($observation);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $ObservationModel->save($observation);
                $this->getServiceLocator()->get('computedFields')
                    ->calculateAllForObservation($observation);

                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Data saved.');
                return $this->redirect()->toRoute(
                    'observation/group',
                    array(
                        'id' => $observation->getId(),
                        'benchmarkGroupId' => $benchmarkGroup->getId()
                    )
                );
            }

        }

        return array(
            'form' => $form,
            'observation' => $observation
        );
    }

    public function overviewAction()
    {
        // Handle system admins
        $user = $this->zfcUserAuthentication()->getIdentity();
        if ($user->getRole() == 'system_admin'
            && empty($this->getSystemAdminSessionContainer()->college)) {
            return $this->systemadminoverviewAction();
        }

        // Regular users
        $currentStudy = $this->currentStudy();
        $benchmarkGroups = $currentStudy->getBenchmarkGroups();
        $observation = $this->getCurrentObservation();
        $completionPercentage = $currentStudy
            ->getCompletionPercentage($observation);

        return array(
            'currentStudy' => $currentStudy,
            'benchmarkGroups' => $benchmarkGroups,
            'observation' => $observation,
            'completionPercentage' => $completionPercentage
        );
    }

    public function systemadminoverviewAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();
        $system = $user->getCollege()->getSystem();

        if (empty($system)) {
            throw new \Exception('System not found');
        }

        $currentStudy = $this->currentStudy();

        $view = new ViewModel(
            array(
                'currentStudy' => $currentStudy,
                'system' => $system
            )
        );
        $view->setTemplate('mrss/observation/systemadminoverview.phtml');

        return $view;
    }

    /**
     * Allow a system admin to switch the college they're entering data for
     */
    public function switchAction()
    {
        $collegeId = $this->params('college_id');
        if (empty($collegeId)) {
            $collegeId = $this->params()->fromQuery('college_id');
        }

        // Clear active college and return to system overview
        if ($collegeId == 'overview') {
            $this->getSystemAdminSessionContainer()->college = null;

            return $this->redirect()->toRoute('data-entry');
        }

        // Make sure that this college belongs to the right system
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $college = $collegeModel->find($collegeId);
        $targetSystem = $college->getSystem();
        $user = $this->zfcUserAuthentication()->getIdentity();
        $userSystem = $user->getCollege()->getSystem();
        $role = $user->getRole();

        if (empty($targetSystem) || empty($userSystem) || $role != 'system_admin'
            || $userSystem != $targetSystem) {
            throw new \Exception(
                'You do not have permission to enter data for that college'
            );
        }

        // Set the session variable
        $this->getSystemAdminSessionContainer()->college = $collegeId;


        // Redirect to the referrer
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }

    /**
     * Return the user's college or the active college for a system_admin
     */
    public function getActiveCollege()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();

        if ($user->getRole() == 'system_admin'
            && !empty($this->getSystemAdminSessionContainer()->college)) {
            $collegeModel = $this->getServiceLocator()->get('model.college');
            $college = $collegeModel->find(
                $this->getSystemAdminSessionContainer()->college
            );
        } else {
            $college = $user->getCollege();
        }

        return $college;
    }

    public function getCurrentObservation()
    {
        // Find the observation by the year and the user's college
        $collegeId = $this->getActiveCollege()->getId();

        $year = $this->currentStudy()->getCurrentYear();

        $ObservationModel = $this->getServiceLocator()->get('model.observation');
        /** @var \Mrss\Entity\Observation $observation */
        $observation = $ObservationModel->findOne($collegeId, $year);

        if (empty($observation)) {
            throw new \Exception('Unable to get current observation.');
        }

        return $observation;
    }

    public function dataEntryAction()
    {
        // Fetch the form
        $benchmarkGroupId = $this->params('benchmarkGroup');
        $benchmarkGroup = $this->getServiceLocator()
            ->get('model.benchmarkGroup')
            ->find($benchmarkGroupId);

        if (empty($benchmarkGroup)) {
            throw new \Exception('Benchmark group not found');
        }

        $observation = $this->getCurrentObservation();

        $formService = $this->getServiceLocator()
            ->get('service.formBuilder');
        $form = $formService->buildForm($benchmarkGroup, $observation->getYear());

        $form->setAttribute('class', 'form-horizontal');

        // bind observation to form, which will populate it with values
        $form->bind($observation);

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $ObservationModel = $this->getServiceLocator()->get('model.observation');
                $ObservationModel->save($observation);
                $this->getServiceLocator()->get('computedFields')
                    ->calculateAllForObservation($observation);

                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Data saved.');
                return $this->redirect()->toRoute('data-entry');
            }

        }

        return array(
            'form' => $form,
            'observation' => $observation,
            'benchmarkGroup' => $benchmarkGroup
        );
    }

    public function importAction()
    {
        // Get the import form
        $form = new \Mrss\Form\ImportData('import');

        $errorMessages = array();

        // Handle the form
        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();

                /**
                 * Steps:
                 *
                 * Get data from excel
                 * Check for errors using inputFilters
                 * if there are errors, stop and show them
                 * if not, merge into observation and save
                 */

                try {
                    $filename = $data['file']['tmp_name'];
                    $excelService = new \Mrss\Service\Excel();
                    $data = $excelService->getObservationDataFromExcel($filename);
                } catch (\Exception $exception) {
                    $this->flashMessenger()->addErrorMessage(
                        'There was a problem processing your import file. Try ' .
                        'downloading the export file again. If you continue to ' .
                        'have trouble, contact us.'
                    );

                    return $this->redirect()->toRoute('data-entry/import');
                }


                $inputFilter = $this->currentStudy()->getInputFilter();

                /** We'll need the benchmark model so we can look up
                 * the labels of any invalid ones
                 *
                 * @var \Mrss\Model\Benchmark $benchmarkModel
                 */
                $benchmarkModel = $this->getServiceLocator()->get('model.benchmark');

                $inputFilter->setData($data);

                // Is the data in the Excel file valid?
                if ($inputFilter->isValid()) {
                    // Now we actually save the data to the observation
                    $observation = $this->getCurrentObservation();

                    foreach ($data as $column => $value) {
                        try {
                            $observation->set($column, $value);
                        } catch (\Exception $exception) {
                            $this->flashMessenger()->addErrorMessage(
                                'There was a problem importing your file. Please ' .
                                'try again or contact us.'
                            );

                            return $this->redirect()->toRoute('data-entry/import');
                        }
                    }

                    $observationModel = $this->getServiceLocator()
                        ->get('model.observation');
                    $observationModel->save($observation);
                    $this->getServiceLocator()->get('em')->flush();

                    $this->flashMessenger()->addSuccessMessage("Data imported.");
                    return $this->redirect()->toRoute('data-entry');
                } else {
                    foreach ($inputFilter->getInvalidInput() as $error) {
                        // Get the benchmark so we can show the label in the error
                        $benchmark = $benchmarkModel->findOneByDbColumn(
                            $error->getName()
                        );

                        $message = implode(
                            ', ',
                            $error->getMessages()
                        );
                        $message .= '. Your value: ' . $data[$error->getName()];

                        $errorMessages[$benchmark->getName()] = $message;
                    }

                    $this->flashMessenger()->addErrorMessage(
                        "Your data was not imported. Please correct the errors below
                        and try again."
                    );
                }
            }
        }

        return array(
            'form' => $form,
            'errorMessages' => $errorMessages
        );
    }

    public function exportAction()
    {
        $collegeId = $this->getActiveCollege()->getId();

        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $subscription = $subscriptionModel->findCurrentSubscription(
            $this->currentStudy(),
            $collegeId
        );

        if (empty($subscription)) {
            throw new \Exception(
                'Unable to download Excel file. Subscription not found.'
            );
        }

        $excelService = new \Mrss\Service\Excel();
        $excelService->getExcelForSubscription($subscription);

    }

    public function importsystemAction()
    {
        // Get the import form
        $form = new \Mrss\Form\ImportData('import');

        $errorMessages = array();

        return array(
            'form' => $form,
            'errorMessages' => $errorMessages
        );
    }

    public function exportsystemAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();
        $system = $user->getCollege()->getSystem();
        $study = $this->currentStudy();
        $subscriptions = $system->getSubscriptionsByStudyAndYear(
            $study->getId(),
            $study->getCurrentYear()
        );

        $excelService = new \Mrss\Service\Excel();
        $excelService->getExcelForSubscriptions($subscriptions);
    }

    /**
     * Get field metadata from the benchmark entity
     *
     * @param integer $year
     * @param \Mrss\Entity\BenchmarkGroup $benchmarkGroup
     * @return array
     */
    protected function getFields($year, $benchmarkGroup = null)
    {
        if (empty($benchmarkGroup)) {
            // Get them all
            $benchmarkModel = $this->getServiceLocator()->get('model.benchmark');

            $benchmarks = $benchmarkModel->findAll();
        } else {
            $benchmarks = $benchmarkGroup->getBenchmarksForYear($year);
        }


        $fields = array();
        foreach ($benchmarks as $benchmark) {
            $fields[$benchmark->getDbColumn()] = $benchmark->getName();
        }

        return $fields;
    }

    public function getSystemAdminSessionContainer()
    {
        if (empty($this->systemAdminSessionContainer)) {
            $container = new Container('system_admin');
            $this->systemAdminSessionContainer = $container;
        }

        return $this->systemAdminSessionContainer;
    }
}
