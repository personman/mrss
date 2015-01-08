<?php


namespace Mrss\Controller;

use Mrss\Form\ImportData;
use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Entity\Observation;
use Mrss\Entity\SubObservation;
use Mrss\Service\Excel;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class ObservationController extends AbstractActionController
{
    protected $systemAdminSessionContainer;

    public function viewAction()
    {
        $observationId = $this->params('id');
        $ObservationModel = $this->getServiceLocator()->get('model.observation');

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
            'benchmarkGroups' => $this->currentStudy()->getBenchmarkGroups(),
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
        $college = $this->currentCollege();

        /*
        $user = $this->zfcUserAuthentication()->getIdentity();
        $userModel = $this->getServiceLocator()->get('model.user');
        $user = $userModel->find($user->getId());

        if ($user->getRole() == 'system_admin'
            && !empty($this->getSystemAdminSessionContainer()->college)) {
            $collegeModel = $this->getServiceLocator()->get('model.college');
            $college = $collegeModel->find(
                $this->getSystemAdminSessionContainer()->college
            );
        } else {
            $college = $user->getCollege();
        }*/

        return $college;
    }

    /**
     *
     * @return \Mrss\Entity\Observation
     * @throws \Exception
     */
    public function getCurrentObservation()
    {
        $observation = $this->currentObservation();

        return $observation;
    }

    public function getCurrentObservationByIpeds($ipeds)
    {
        /** @var \Mrss\Model\College $collegeModel */
        $collegeModel = $this->getServiceLocator()->get('model.college');
        $college = $collegeModel->findOneByIpeds($ipeds);

        if (empty($college)) {
            throw new \Exception('No college found for ipeds id: ' . $ipeds);
        }

        $year = $this->currentStudy()->getCurrentYear();

        $observationModel = $this->getServiceLocator()->get('model.observation');

        /** @var \Mrss\Entity\Observation $observation */
        $observation = $observationModel->findOne($college->getId(), $year);

        if (empty($observation)) {
            throw new \Exception('Unable to get current observation.');
        }

        return $observation;
    }

    public function dataEntryAction()
    {
        // Fetch the form
        $benchmarkGroupId = $this->params('benchmarkGroup');

        /** @var \Mrss\Entity\BenchmarkGroup $benchmarkGroup */
        $benchmarkGroup = $this->getServiceLocator()
            ->get('model.benchmarkGroup')
            ->find($benchmarkGroupId);

        if (empty($benchmarkGroup)) {
            throw new \Exception('Benchmark group not found');
        }

        $dataEntryOpen = $this->currentStudy()->getDataEntryOpen();

        // SubObs?
        if ($benchmarkGroup->getUseSubObservation()) {
            return $this->listSubObservations($benchmarkGroup);
        }

        try {
            $observation = $this->getCurrentObservation();
        } catch (\Exception $e) {
            // If the observation is not found, check a prior year
            if (empty($observation)) {
                /** @var \Mrss\Model\Observation $ObservationModel */
                $ObservationModel = $this->getServiceLocator()->get('model.observation');
                $observation = $ObservationModel->findOne(
                    $this->currentCollege(),
                    $this->currentStudy()->getCurrentYear() - 1
                );

                if (empty($observation)) {
                    $this->flashMessenger()->addErrorMessage(
                        'There was a problem retrieving the observation.'
                    );

                    return $this->redirect()->toUrl('/');
                }
            }

        }

        $oldObservation = clone $observation;

        $formService = $this->getServiceLocator()
            ->get('service.formBuilder');
        $form = $formService->buildForm(
            $benchmarkGroup,
            $observation->getYear(),
            !$dataEntryOpen
        );


        $class = 'form-horizontal ' . $benchmarkGroup->getFormat();

        $form->setAttribute('class', $class);



        // bind observation to form, which will populate it with values
        $form->bind($observation);

        // Hard-coded binding of best practices. @todo: make this more elegant
        if ($form->has('best_practices')) {
            $bp = $form->get('best_practices');
            $bp->setValue(explode("\n", $bp->getValue()));
            //prd($_POST);
        }

        // Handle form submission
        if ($this->getRequest()->isPost()) {
            // Is data entry open?
            if (!$dataEntryOpen) {
                $this->flashMessenger()->addErrorMessage('Data entry is closed.');
                return $this->redirect()->toRoute('data-entry');
            }


            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            //var_dump($form->getElements()); die;
            if ($form->isValid()) {
                // This may take a minute
                ini_set('memory_limit', '512M');
                set_time_limit(3600);


                $ObservationModel = $this->getServiceLocator()->get('model.observation');
                $ObservationModel->save($observation);

                $this->getServiceLocator()->get('service.observationAudit')
                    ->logChanges($oldObservation, $observation, 'dataEntry');

                $this->getServiceLocator()->get('computedFields')
                    ->calculateAllForObservation($observation);

                if ($benchmarkGroup->getUseSubObservation()) {
                    $this->mergeAllSubobservations();
                }

                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Data saved.');
                return $this->redirect()->toRoute('data-entry');
            }

        }

        // Is the college subscribed to NCCBP for this year
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $nccbpSubscription = $subscriptionModel->findOne(
            $observation->getYear(),
            $observation->getCollege()->getId(),
            1 // NCCBP
        );

        $view = new ViewModel(
            array(
                'form' => $form,
                'observation' => $observation,
                'benchmarkGroup' => $benchmarkGroup,
                'nccbpSubscription' => $nccbpSubscription,
                'variable' => $this->getVariableSubstitutionService()
            )
        );

        $this->checkForCustomTemplate($benchmarkGroup, $view);

        return $view;
    }

    public function mergeAllSubobservations()
    {
        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        $subscriptions = $study->getSubscriptions();
        foreach ($subscriptions as $subscription) {
            $observation = $subscription->getObservation();

            if ($observation) {
                $observation->mergeSubobservations();
            } else {
                prd($subscription->getId());
            }

        }
    }

    public function listSubObservations($benchmarkGroup)
    {
        $observation = $this->getCurrentObservation();

        $view = new ViewModel(
            array(
                'benchmarkGroup' => $benchmarkGroup,
                'observation' => $observation,
                'maxAcademicUnits' => 10
            )
        );

        $view->setTemplate('mrss/sub-observation/index.phtml');

        return $view;
    }

    /**
     * If a custom template is set up for this benchmarkGroup, switch to it.
     * Get the id and file name from config (differs in production)
     */
    public function checkForCustomTemplate($benchmarkGroup, $view)
    {
        $config = $this->getServiceLocator()->get('Config');

        $id = $benchmarkGroup->getId();
        $shortName = $benchmarkGroup->getShortName();

        // Do we have a config for the grouped template?
        if (false && !empty($config['data-entry']['grouped'][$shortName])) {
            $groupedConfig = $this->getGroupedConfig($shortName);
            $template = 'grouped.phtml';
            $view->setTemplate('mrss/observation/' . $template);
            $view->setVariable('groupedConfig', $groupedConfig);

        } elseif (!empty($config['data_entry_templates'][$id])) {
            $template = $config['data_entry_templates'][$id];
            $view->setTemplate('mrss/observation/' . $template);
        }
    }

    public function getGroupedConfig($shortName)
    {
        $config = $this->getServiceLocator()->get('Config');
        $groupedConfig = $config['data-entry']['grouped'][$shortName];

        foreach ($groupedConfig as $key => $groupConf) {
            $groupConf['title'] = $this->getVariableSubstitutionService()
                ->substitute($groupConf['title']);
            $groupConf['description'] = $this->getVariableSubstitutionService()
                ->substitute($groupConf['description']);
            $groupedConfig[$key] = $groupConf;
        }

        return $groupedConfig;
    }

    public function importAction()
    {
        // Get the import form
        $form = new ImportData('import');

        $errorMessages = array();

        /** @var \Mrss\Entity\Study $study */
        $study = $this->currentStudy();

        // Handle the form
        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            // Is data entry open?
            if (!$study->getDataEntryOpen()) {
                $this->flashMessenger()->addErrorMessage('Data entry is closed.');
                return $this->redirect()->toRoute('data-entry/import');
            }


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
                    $excelService = new Excel();
                    $excelService->setCurrentStudy($study);
                    $excelService->setCurrentCollege($this->currentCollege());

                    $allData = $excelService->getObservationDataFromExcel($filename);
                } catch (\Exception $exception) {
                    //var_dump($exception->getMessage()); die;
                    $this->flashMessenger()->addErrorMessage(
                        'There was a problem processing your import file. Try ' .
                        'downloading the export file again. If you continue to ' .
                        'have trouble, contact us. ' . $exception->getMessage()
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

                /** @var \Mrss\Model\College $collegeModel */
                $collegeModel = $this->getServiceLocator()->get('model.college');

                $observationModel = $this->getServiceLocator()
                    ->get('model.observation');

                foreach ($allData as $ipeds => $data) {
                    $college = $collegeModel->findOneByIpeds($ipeds);

                    // Make the sure the user has permission to do data-entry
                    // for this college
                    $this->checkPermissionsForImport($college);

                    $collegeErrorMessages = array();
                    $inputFilter->setData($data);

                    // Is the data in the Excel file valid?
                    if ($inputFilter->isValid()) {
                        // Now we actually save the data to the observation
                        $observation = $this->getCurrentObservationByIpeds($ipeds);

                        // Clone for logging
                        $oldObservation = clone $observation;

                        // Handle any subobservations
                        if (!empty($data['subobservations'])) {
                            $this->saveSubObservations(
                                $data['subobservations'],
                                $observation
                            );

                            unset($data['subobservations']);
                        }

                        foreach ($data as $column => $value) {
                            if ($column == 'subobservations') {
                                continue;
                            }

                            try {
                                $observation->set($column, $value);
                            } catch (\Exception $exception) {
                                $this->flashMessenger()->addErrorMessage(
                                    'There was a problem importing your file. ' .
                                    'Please try again or contact us. ' .
                                    $exception->getMessage()
                                );

                                return $this->redirect()->toRoute('data-entry/import');
                            }
                        }

                        $observationModel->save($observation);
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

                            $collegeErrorMessages[$benchmark->getName()] = $message;
                        }

                        $errorMessages[] = array(
                            'college' => $college->getName(),
                            'errors' => $collegeErrorMessages
                        );
                    }
                }
            }

            // How did that go?
            if (empty($errorMessages)) {
                // Merge subobservations
                $observation->mergeSubobservations();

                // Log any changes
                $this->getServiceLocator()->get('service.observationAudit')
                    ->logChanges($oldObservation, $observation, 'excel');

                // No errors, time to save to the db
                $this->getServiceLocator()->get('em')->flush();
                $this->flashMessenger()->addSuccessMessage("Data imported.");
                return $this->redirect()->toRoute('data-entry');
            } else {
                // Something went wrong. We'll show the error messages
                $this->flashMessenger()->addErrorMessage(
                    "Your data was not imported. Please correct the errors below
                    and try again."
                );
            }
        }

        return array(
            'form' => $form,
            'errorMessages' => $errorMessages
        );
    }

    public function saveSubObservations($data, Observation $observation)
    {
        $subObservations = $observation->getSubObservations();
        $subObservationModel = $this->getServiceLocator()->get('model.subobservation');

        $i = 0;
        foreach ($data as $subObData) {
            if (empty($subObservations[$i])) {
                $subObservation = new SubObservation;
                $subObservation->setObservation($observation);
            } else {
                $subObservation = $subObservations[$i];
            }

            // Clone for logging
            $oldSubObservation = clone $subObservation;

            // Set the data values
            foreach ($subObData as $dbColumn => $value) {
                if ($subObservation->has($dbColumn)) {
                    $subObservation->set($dbColumn, $value);
                }
            }

            // Log any changes
            $this->getServiceLocator()->get('service.observationAudit')
                ->logSubObservationChanges($oldSubObservation, $subObservation, 'excel');

            // Save subobservation
            $subObservationModel->save($subObservation);
            $i++;
        }
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

        $excelService = new Excel();
        $excelService->setBenchmarkModel(
            $this->getServiceLocator()->get('model.benchmark')
        );
        $excelService->setCurrentStudy($this->currentStudy());
        $excelService->getExcelForSubscriptions(array($subscription));
    }

    public function importsystemAction()
    {
        return $this->importAction();


        // Get the import form
        $form = new ImportData('import');

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
     * A user is trying to import to this college. Make sure they're allowed. They
     * should be either
     * a) a user on the college (most common case)
     * b) a system admin user in the same system as the college, or
     * c) an admin user (those cats can do anything)
     *
     * @param \Mrss\Entity\College $college
     * @throws \Exception
     */
    public function checkPermissionsForImport(\Mrss\Entity\College $college)
    {
        $user = $this->zfcUserAuthentication()->getIdentity();

        $authorized = false;

        // Do they belong to the college in question
        if ($user->getCollege()->getId() == $college->getId()) {
            $authorized = true;
        }

        // Is the college part of a system?
        if ($collegeSystem = $college->getSystem()) {
            // Is the user a system admin in that same system?
            if ($user->getRole() == 'system_admin') {
                $userSystem = $user->getCollege()->getSystem();
                if (!empty($userSystem)
                    && $userSystem->getId() == $collegeSystem->getId()
                ) {
                    $authorized = true;
                }
            }
        }

        // Finally, is the user an admin
        if ($user->getRole() == 'admin') {
            $authorized = true;
        }

        if (!$authorized) {
            throw new \Exception(
                'You are not authorized to manage data for ' .
                $college->getName()
            );
        }
    }

    public function allAction()
    {
        $currentStudy = $this->currentStudy();
        $benchmarkGroups = $currentStudy->getBenchmarkGroups();
        $observation = $this->getCurrentObservation();

        return array(
            'study' => $currentStudy,
            'observation' => $observation
        );
    }

    public function getYearFromRouteOrStudy()
    {
        $year = $this->params()->fromRoute('year');

        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();

            // Submitted values are for last year's data, until reports open
            $college = $this->currentCollege();
            if (!$this->currentStudy()->getReportsOpen()) {
                $year = $year - 1;
            }
        }

        return $year;
    }

    public function submittedValuesAction()
    {
        $year = $this->getYearFromRouteOrStudy();

        // Get their subscriptions
        $subscriptions = $this->currentCollege()
            ->getSubscriptionsForStudy($this->getCurrentStudy());

        // Get the observation
        /** @var \Mrss\Model\Observation $ObservationModel */
        $ObservationModel = $this->getServiceLocator()->get('model.observation');
        $observation = $ObservationModel->findOne(
            $this->currentCollege(),
            $year
        );

        // We'll use the report service to determine decimal places
        $reportService = $this->getServiceLocator()->get('service.report');

        if (empty($observation)) {
            $collegeId = $this->currentCollege()->getId();
            /*throw new \Exception(
                "Observation not found for college $collegeId and year $year."
            );*/

            return $this->observationNotFound();
        }

        $variable = $this->getVariableSubstitutionService();
        $variable->setStudyYear($year);

        $submittedValues = array();

        // Get the benchmark groups
        $benchmarkGroups = $this->getCurrentStudy()->getBenchmarkGroups();
        foreach ($benchmarkGroups as $benchmarkGroup) {
            $groupData = array(
                'benchmarkGroup' => $benchmarkGroup->getName(),
                'benchmarks' => array()
            );
            $benchmarks = $benchmarkGroup->getChildren($year);

            foreach ($benchmarks as $benchmark) {
                if (get_class($benchmark) == 'Mrss\Entity\BenchmarkHeading') {
                    /** @var \Mrss\Entity\BenchmarkHeading $heading */
                    $heading = $benchmark;
                    $groupData['benchmarks'][] = array(
                        'heading' => true,
                        'name' => $variable->substitute($heading->getName()),
                        'description' => $variable->substitute($heading->getDescription())
                    );
                    continue;
                }

                $prefix = $suffix = '';
                if ($benchmark->isPercent()) {
                    $suffix = '%';
                } elseif ($benchmark->isDollars()) {
                    $prefix = '$';
                }

                $value = $observation->get($benchmark->getDbColumn());
                if (!is_null($value) && $benchmark->getInputType() != 'radio') {
                    $decimalPlaces = $reportService->getDecimalPlaces($benchmark);
                    $value = $prefix .
                        number_format($value, $decimalPlaces) .
                        $suffix;
                }
                $groupData['benchmarks'][] = array(
                    'benchmark' => $benchmark,
                    'value' => $value
                );
            }

            $submittedValues[] = $groupData;
        }

        return array(
            'subscriptions' => $subscriptions,
            'year' => $year,
            'submittedValues' => $submittedValues,
            'variable' => $variable
        );
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

    /**
     * @return \Mrss\Service\VariableSubstitution
     */
    public function getVariableSubstitutionService()
    {
        return $this->getServiceLocator()->get('service.variableSubstitution');
    }

    /**
     * @return \Mrss\Entity\Study
     */
    protected function getCurrentStudy()
    {
        return $this->currentStudy();
    }

    public function observationNotFound()
    {
        $this->flashMessenger()->addErrorMessage(
            'Unable to find membership.'
        );
        return $this->redirect()->toUrl('/members');
    }
}
