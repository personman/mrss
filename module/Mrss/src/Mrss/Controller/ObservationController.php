<?php


namespace Mrss\Controller;

use Mrss\Form\ImportData;
use Mrss\Service\DataEntryHydrator;
use Mrss\Entity\Observation;
use Mrss\Entity\SubObservation;
use Mrss\Service\Excel;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\Form;
use Zend\Form\Element;
use PHPExcel;
use PHPExcel_Worksheet;
use PHPExcel_IOFactory;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Alignment;
use PHPExcel_Shared_Font;

class ObservationController extends BaseController
{
    protected $systemAdminSessionContainer;

    protected $currentObservation;

    public function viewAction()
    {
        $observationId = $this->params('id');
        $ObservationModel = $this->getServiceLocator()->get('model.observation');

        $benchmarkGroupUrl = $this->params('benchmarkGroup');
        if (!empty($benchmarkGroupUrl)) {
            $benchmarkGroup = $this->getBenchmarkGroupModel()
                ->findOneByUrlAndStudy($benchmarkGroupUrl, $this->currentStudy());
        } else {
            $benchmarkGroup = null;
        }

        $observation = $ObservationModel->find($observationId);

        if (1 && !empty($benchmarkGroup)) {
            // Don't allow saving
            if ($this->getRequest()->isPost()) {
                $this->flashMessenger()->addErrorMessage(
                    'You are not allowed to save data in this view. Impersonate to make edits to data.'
                );
                return $this->redirect()->toRoute('observation', array('id' => $observationId));
            }
            $this->setCurrentObservation($observation);

            return $this->dataEntryAction(true);
        }


        return array(
            'observation' => $observation,
            'benchmarkGroups' => $this->currentStudy()->getBenchmarkGroups(),
            'benchmarkGroup' => $benchmarkGroup,
            'fields' => $this->getFields($observation->getYear(), $benchmarkGroup)
        );
    }

    /**
     * @return \Mrss\Model\BenchmarkGroup
     */
    public function getBenchmarkGroupModel()
    {
        return $this->getServiceLocator()->get('model.benchmark.group');
    }

    public function editAction()
    {
        $observationId = $this->params('id');
        $benchmarkGroupId = $this->params('benchmarkGroup');

        $ObservationModel = $this->getServiceLocator()->get('model.observation');
        $observation = $ObservationModel->find($observationId);

        $benchmarkGroup = $this->getServiceLocator()
            ->get('model.benchmark.group')
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

    protected function redirectIfNoMembership()
    {
        // Do they have a membership?
        $membership = $this->getMembership();

        if (empty($membership)) {
            $this->flashMessenger()
                ->addErrorMessage("You need to renew your membership before you can enter data.");

            return $this->redirect()->toUrl('/renew');
        }
    }

    protected function getMembership()
    {
        $year = $this->currentStudy()->getCurrentYear();
        $membership = $this->getSubscriptionModel()->findOne(
            $year,
            $this->currentCollege()->getId(),
            $this->currentStudy()->getId()
        );

        return $membership;
    }

    public function overviewAction()
    {
        if ($redirect = $this->redirectIfNoMembership()) {
            return $redirect;
        }

        // Handle system admins
        $user = $this->zfcUserAuthentication()->getIdentity();
        if ($user->getRole() == 'system_admin'
            && empty($this->getSystemAdminSessionContainer()->college)) {
            return $this->systemadminoverviewAction();
        }

        // Regular users
        /** @var \Mrss\Entity\Study $currentStudy */
        $currentStudy = $this->currentStudy();
        $membership = $this->getMembership();
        $benchmarkGroups = $this->getBenchmarkGroups();
        $observation = $this->getCurrentObservation();
        $completionPercentage = $currentStudy
            ->getCompletionPercentage($observation);

        if ($completionPercentage > 100) {
            $completionPercentage = 100;
        }

        $issues = $this->getIssueModel()->findForCollege($this->currentCollege());

        return array(
            'currentStudy' => $currentStudy,
            'benchmarkGroups' => $benchmarkGroups,
            'observation' => $observation,
            'issues' => $issues,
            'completionPercentage' => $completionPercentage,
            'subscription' => $membership
        );
    }

    /**
     * @return \Mrss\Entity\Structure
     */
    protected function getStructure()
    {
        //@todo: get from session
        $currentSystem = $this->getActiveSystem();

        $system = $this->getSystemModel()->find($currentSystem);
        $structure = $system->getDataEntryStructure();

        return $structure;
    }

    protected function getBenchmarkGroups()
    {
        if ($this->getStudyConfig()->use_structures) {
            $structure = $this->getStructure();
            $benchmarkGroups = $structure->getPages();

        } else {
            $currentStudy = $this->currentStudy();
            $membership = $this->getMembership();
            $benchmarkGroups = $currentStudy->getBenchmarkGroupsBySubscription($membership);
        }

        return $benchmarkGroups;
    }

    /**
     * @return \Mrss\Model\Issue
     */
    protected function getIssueModel()
    {
        return $this->getServiceLocator()->get('model.issue');
    }

    /**
     * @return \Mrss\Model\System
     */
    protected function getSystemModel()
    {
        return $this->getServiceLocator()->get('model.system');
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

    public function setCurrentObservation(Observation $observation)
    {
        $this->currentObservation = $observation;
    }

    /**
     *
     * @return \Mrss\Entity\Observation
     * @throws \Exception
     */
    public function getCurrentObservation()
    {
        if (empty($this->currentObservation)) {
            $this->currentObservation = $this->currentObservation();
        }

        return $this->currentObservation;
    }

    public function getLastYearObservation()
    {
        $year = $this->getCurrentStudy()->getCurrentYear();
        $lastYear = $year - 1;
        $collegeId = $user = $this->zfcUserAuthentication()->getIdentity()->getCollege()->getId();

        /** @var \Mrss\Model\Observation $model */
        $model = $this->getServiceLocator()->get('model.observation');
        return $model->findOne($collegeId, $lastYear);
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

    /**
     * @return \Mrss\Model\Subscription
     */
    protected function getSubscriptionModel()
    {
        /** @var \Mrss\Model\Subscription $subscriptionModel */
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');

        return $subscriptionModel;
    }

    protected function getBenchmarkGroup($url)
    {
        if ($this->getStudyConfig()->use_structures) {
            $structure = $this->getStructure();
            $structure->setPage($url);
            $benchmarkGroup = $structure->getBenchmarkGroup();
        } else {
            /** @var \Mrss\Entity\BenchmarkGroup $benchmarkGroup */
            $benchmarkGroup = $this->getServiceLocator()
                ->get('model.benchmark.group')
                ->findOneByUrlAndStudy($url, $this->currentStudy());
        }

        return $benchmarkGroup;
    }

    public function dataEntryAction($staffView = false)
    {
        // Fetch the form
        $benchmarkGroupUrl = $this->params('benchmarkGroup');

        $benchmarkGroup = $this->getBenchmarkGroup($benchmarkGroupUrl);

        $subscriptionModel = $this->getSubscriptionModel();

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

        // Phasing out observation for subscripton
        $subscription = $observation->getSubscription();

        $oldData = $subscription->getAllData();

        // Clone the unedited observation for comparison
        //$oldObservation = clone $observation;

        /** @var \Mrss\Service\FormBuilder $formService */
        $formService = $this->getServiceLocator()
            ->get('service.formBuilder');


        // If they entered data last year, populate it here for the help-block
        if ($lastYearObservation = $this->getLastYearObservation()) {
            $formService->setLastYearObservation($lastYearObservation);
        }

        $form = $formService->buildForm(
            $benchmarkGroup,
            $observation->getYear(),
            !$dataEntryOpen
        );


        $class = 'data-entry-form form-horizontal ' . $benchmarkGroup->getFormat();

        $form->setAttribute('class', $class);

        // bind observation to form, which will populate it with values
        $form->bind($observation);

        $form = $this->copyCampusInfoFromLastYear($form);

        // Hard-coded binding of best practices. @todo: make this more elegant
        if ($form->has('best_practices')) {
            $bp = $form->get('best_practices');
            $bpValue = explode("\n", $bp->getValue());
            $bp->setValue($bpValue);
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
            $formData = $this->params()->fromPost();
            unset($formData['buttons']);

            $form->setData($formData);


            //var_dump($form->getElements()); die;
            if ($form->isValid()) {
                // This may take a minute
                takeYourTime();

                $ObservationModel = $this->getServiceLocator()->get('model.observation');
                $ObservationModel->save($observation);

                //$changeSet = $this->getServiceLocator()->get('service.observationAudit')
                //    ->logChanges($oldObservation, $observation, 'dataEntry');

                $newData = $subscription->getAllData();

                //pr($newData);
                //pr($newData['institution_conversion_factor']);
                //pr($subscription->getValue('institution_conversion_factor'));
                //pr($subscription->getId());

                /** @var \Mrss\Service\ObservationAudit $observationAudit */
                $observationAudit = $this->getServiceLocator()->get('service.observationAudit');
                $changeSet = $observationAudit
                    ->logChangesNew($oldData, $newData, 'dataEntry', $subscription);


                $this->getServiceLocator()->get('computedFields')
                    ->calculateAllForObservation($observation);

                $validationService = $this->getServiceLocator()->get('service.validation');
                $validationService->setChangeSet($changeSet);
                $issues = $validationService->validate($observation);

                if ($benchmarkGroup->getUseSubObservation()) {
                    $this->mergeAllSubobservations();
                }

                // Calculate completion
                $subscription->updateCompletion();
                $this->getSubscriptionModel()->save($subscription);
                $this->getSubscriptionModel()->getEntityManager()->flush();

                //$this->updateCompletion($observation);

                $redirect = $this->params()->fromPost('redirect', '/data-entry');

                // Save and edit button?
                $data = $this->params()->fromPost();
                if (!empty($data['buttons']['save-edit'])) {
                    $redirect = '/data-entry/' . $benchmarkGroup->getUrl();
                }

                if (empty($issues)) {
                    $this->flashMessenger()->addSuccessMessage('Data saved.');
                } else {
                    $this->flashMessenger()->addErrorMessage(
                        $this->getValidationIssuesMessage($issues)
                    );
                }
                return $this->redirect()->toUrl($redirect);
            }

        }

        // Is the college subscribed to NCCBP for this year
        $nccbpSubscription = $subscriptionModel->findOne(
            $observation->getYear(),
            $observation->getCollege()->getId(),
            1 // NCCBP
        );

        $conversionFactor = 1;
        if ($this->currentStudy()->getId() == 4 && $observation->has('institution_conversion_factor')) {
            $conversionFactor = $observation->get('institution_conversion_factor');
        }

        $view = new ViewModel(
            array(
                'form' => $form,
                'observation' => $observation,
                'benchmarkGroup' => $benchmarkGroup,
                'benchmarkGroups' => $this->getBenchmarkGroups(),
                'nccbpSubscription' => $nccbpSubscription,
                'variable' => $this->getVariableSubstitutionService(),
                'dataDefinitionForm' => $this->getDataDefinitionForm(),
                'dataEntryLayout' => $this->getDataEntryLayout($benchmarkGroup),
                'staffView' => $staffView,
                'conversionFactor' => $conversionFactor
            )
        );

        $view->setTemplate('mrss/observation/data-entry.phtml');

        $this->checkForCustomTemplate($benchmarkGroup, $view);

        return $view;
    }

    protected function updateCompletion($observation)
    {
        // Calculate completion
        $completion = $this->currentStudy()->getCompletionPercentage($observation);

        $subscription = $this->getSubscriptionModel()
            ->findOne(
                $observation->getYear(),
                $observation->getCollege(),
                $this->currentStudy()->getId()
            );
        $subscription->setCompletion($completion);

        $this->getServiceLocator()->get('em')->flush();
    }

    protected function getValidationIssuesMessage($issues)
    {
        $count = count($issues);
        $noun = ($count == 1) ? 'problem' : 'problems';

        $message = "Your data was saved but we identified $count potential $noun with it.
                        <a href='/issues'>Please review</a>.";

        return $message;
    }

    protected function getStudyConfig()
    {
        return $this->getServiceLocator()->get('Study');
    }

    protected function getDataEntryLayout($benchmarkGroup)
    {
        $studyConfig = $this->getStudyConfig();

        /** @var \Zend\Config\Config $dataEntryLayouts */
        $dataEntryLayouts = $studyConfig->data_entry_layout;
        $dataEntryLayouts = $dataEntryLayouts->toArray();

        $layout = null;
        if (!empty($dataEntryLayouts[$benchmarkGroup->getId()])) {
            $layout = $dataEntryLayouts[$benchmarkGroup->getId()];
        }

        return $layout;
    }

    /**
     * @param \Zend\Form\Form $form
     * @return mixed
     */
    protected function copyCampusInfoFromLastYear($form)
    {
        if ($lastYearObservation = $this->getLastYearObservation()) {
            foreach ($this->getCampusBenchmarksToCopyFromLastYear() as $dbColumn) {
                if ($form->has($dbColumn)) {
                    $field = $form->get($dbColumn);
                    $value = $field->getValue();

                    if (is_null($value)) {
                        $oldValue = $lastYearObservation->get($dbColumn);
                        $field->setValue($oldValue);
                    }
                }
            }
        }

        return $form;
    }

    protected function getCampusBenchmarksToCopyFromLastYear()
    {
        return array(
            // NCCBP
            'institutional_type',
            'institutional_demographics_campus_environment',
            'institutional_demographics_faculty_unionized',
            'institutional_demographics_staff_unionized',
            'institutional_control',
            'institutional_demographics_calendar',
            'on_campus_housing',
            'four_year_degrees',

            // AAUP
            'institution_control',
            'institution_sector',
            'institution_aaup_category',
            'institution_campuses',
            'institution_tenure_system',
            'institution_faculty_union',
            'institution_part_time_benefits'
        );
    }

    public function getDataDefinitionForm()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();

        $form = new Form('dataDefinitionForm');

        $form->add(
            array(
                'name' => 'dataDefinitions',
                'type' => 'Select',
                'options' => array(
                    'label' => 'Help text'
                ),
                'attributes' => array(
                    'id' => 'dataDefinitions',
                    'options' => array(
                        'show' => 'Show all data definitions',
                        'active' => 'Show data definitions for active field',
                        'hide' => 'Hide all data definitions',
                    )
                )
            )
        );

        $form->get('dataDefinitions')->setValue($user->getDataDefinitions());

        return $form;
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
                'maxAcademicUnits' => 20
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

        $studyConfig = $this->getStudyConfig();
        $dataEntryTemplates = $studyConfig->data_entry_templates;

        // Do we have a config for the grouped template?
        if (false && !empty($config['data-entry']['grouped'][$shortName])) {
            $groupedConfig = $this->getGroupedConfig($shortName);
            $template = 'grouped.phtml';
            $view->setTemplate('mrss/observation/' . $template);
            $view->setVariable('groupedConfig', $groupedConfig);

        } elseif ($template = $dataEntryTemplates->$id) {
            $view->setTemplate($template);
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
        takeYourTime();

        if ($redirect = $this->redirectIfNoMembership()) {
            return $redirect;
        }

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
                    $excelService->setVariableSubstition($this->getVariableSubstitutionService());
                    $config = $this->getServiceLocator()->get('study');
                    $excelService->setStudyConfig($config);


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
                        // @todo: phasing out observation, plus ipeds could be generic instead
                        $observation = $this->getCurrentObservationByIpeds($ipeds);

                        $subscription = $observation->getSubscription();

                        // Clone for logging
                        $oldObservation = clone $observation;
                        $oldData = $subscription->getAllData();

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
                if ($this->getCurrentStudy()->hasSubobservations()) {
                    $observation->mergeSubobservations();
                }

                // Log any changes
                $this->getServiceLocator()->get('em')->flush();

                $newData = $subscription->getAllData();

                /** @var \Mrss\Service\ObservationAudit $observationAudit */
                $observationAudit = $this->getServiceLocator()->get('service.observationAudit');
                $changeSet = $observationAudit
                    ->logChangesNew($oldData, $newData, 'excel', $subscription);

                // Validate against validation rule class
                $validationService = $this->getServiceLocator()->get('service.validation');
                $validationService->setChangeSet($changeSet);
                $issues = $validationService->validate($observation);

                // No errors, time to save to the db
                $this->getServiceLocator()->get('em')->flush();

                $this->updateCompletion($observation);

                if (empty($issues)) {
                    $this->flashMessenger()->addSuccessMessage("Data imported.");
                } else {
                    $message = $this->getValidationIssuesMessage($issues);
                    $this->flashMessenger()->addErrorMessage($message);
                }

                return $this->redirect()->toRoute('data-entry');
            } else {
                // Something went wrong. We'll show the error messages
                $this->flashMessenger()->addErrorMessage(
                    "Your data was not imported. Please correct the errors below
                    and try again."
                );
            }
        }

        $useDirectDownloadLink = $this->useDirectDownloadLink();


        return array(
            'form' => $form,
            'useDirectDownloadLink' => $useDirectDownloadLink,
            'errorMessages' => $errorMessages
        );
    }

    /**
     * For AAUP, if the institution hasn't entered any data beyond form 1
     *
     * @todo: generalize
     */
    protected function useDirectDownloadLink()
    {
        $direct = false;
        $subscription = $this->getSubscription();

        if ($subscription->getCompletion() > 0) {
            $direct = true;
        }

        return $direct;
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

        $subscriptionModel = $this->getSubscriptionModel();
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
        $excelService->setVariableSubstition($this->getVariableSubstitutionService());

        $config = $this->getServiceLocator()->get('study');
        $excelService->setStudyConfig($config);

        $excelService->getExcelForSubscriptions(array($subscription));
    }

    /**
     * Because of the resources needed to build the file in exportAction(), users with no data can be sent to this
     * action which just downloads the blank excel file rather than converting it to an in-memory object like PHPExcel
     * does.
     */
    public function templateAction()
    {
        // Do they have a custom template?
        $studyConfig = $this->getStudyConfig();
        $filename = $studyConfig->custom_excel_template;

        if ($filename && file_exists($filename)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="data-export.xlsx"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            readfile($filename);
            exit;
        } else {
            return $this->redirect()->toUrl('/data-entry/export');
        }
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
        $excelService->setVariableSubstition($this->getVariableSubstitutionService());
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

    public function getYearFromRouteOrStudy($checkReportOpen = true)
    {
        $year = $this->params()->fromRoute('year');

        if (empty($year)) {
            $year = $this->currentStudy()->getCurrentYear();

            // Are we checking for reports or data entry being open?
            $open = $this->currentStudy()->getReportsOpen();
            if (!$checkReportOpen) {
                $open = $this->currentStudy()->getDataEntryOpen();
            }

            // Submitted values are for last year's data, until reports open
            $college = $this->currentCollege();
            if ($checkReportOpen && !$open) {
                $year = $year - 1;
            }
        }

        return $year;
    }

    public function test2()
    {
        $dbColumn = 'ft_average_no_rank_salary';

        /** @var \Mrss\Model\Benchmark $model */
        $model = $this->getServiceLocator()->get('model.benchmark');
        $benchmark = $model->findOneByDbColumn($dbColumn);

        /** @var \Mrss\Entity\Subscription $subscription */
        $subscription = $this->currentCollege()->getSubscriptionByStudyAndYear($this->currentStudy()->getId(), 2016);

        pr($subscription->getCollege()->getNameAndState());


        $start = microtime(true);
        pr($subscription->getDatum($benchmark)->getValue());
        pr(microtime(true) - $start);

        $start = microtime(true);
        pr($subscription->getDatum($dbColumn)->getValue());
        pr(microtime(true) - $start);

        $start = microtime(true);
        pr($subscription->getValue($dbColumn));
        pr(microtime(true) - $start);



        prd($benchmark->getName());
    }

    public function test()
    {
        // Test stuff
        /** @var \Mrss\Model\Subscription $subModel */
        $subModel = $this->getServiceLocator()->get('model.subscription');

        $dbColumns = array('ft_average_professor_salary', 'ft_average_male_professor_salary');
        $excludeOutliers = true;
        $notNull = true;
        $benchmarkGroupIds = array();
        $system = null;

        $start = microtime(1);
        $subscriptions = $subModel->findWithPartialObservations(
            $this->currentStudy(),
            2016,
            $dbColumns,
            $excludeOutliers,
            $notNull,
            $benchmarkGroupIds,
            $system
        );

        $seconds = round(microtime(1) - $start, 5);
        pr($seconds);


        foreach ($subscriptions as $sub) {
            pr($sub->getCollege()->getNameAndState());
            pr($sub->getObservation()->getId());
            foreach ($dbColumns as $dbColumn) {
                echo $dbColumn;
                pr($sub->getValue($dbColumn));
            }
        }

        $seconds = round(microtime(1) - $start, 5);
        pr($seconds);

        die(' test');
    }

    public function submittedValuesAction()
    {

        $year = $this->getYearFromRouteOrStudy(false);
        $format = $this->params()->fromRoute('format', 'html');

        // Get their subscriptions
        $subscriptions = $this->currentCollege()
            ->getSubscriptionsForStudy($this->getCurrentStudy());

        // Set the year to be the most recent they have a subscription for
        foreach ($subscriptions as $sub) {
            $latestYear = $sub->getYear();
            break;
        }
        reset($subscriptions);
        if ($year > $latestYear) {
            $year = $latestYear;
        }

        // Get the observation
        $subscriptionModel = $this->getSubscriptionModel();
        $subscription = $this->getSubscription($year);

        $observation = $subscription->getObservation();

        // We'll use the report service to determine decimal places
        /** @var \Mrss\Service\Report $reportService */
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


                $value = $observation->get($benchmark->getDbColumn());
                $value = $benchmark->format($value);

                $groupData['benchmarks'][] = array(
                    'benchmark' => $benchmark,
                    'value' => $value,
                    'benchmarkName' => $variable->substitute($benchmark->getReportLabel())
                );
            }

            $submittedValues[] = $groupData;
        }

        if ($format == 'xls') {
            $this->downloadSubmittedValues($submittedValues, $year);
        }

        return array(
            'subscriptions' => $subscriptions,
            'year' => $year,
            'submittedValues' => $submittedValues,
            'completionPercentage' => round($subscription->getCompletion(), 1)
        );
    }

    /**
     * Switch systems and redirect to /data-entry
     */
    public function dataEntrySwitchAction()
    {
        $systemId = $this->params()->fromRoute('systemId');
        $redirect = $this->params()->fromQuery('redirect');
        if (empty($redirect)) {
            $redirect = 'data-entry';
        }

        // Make sure they have access to this system
        if ($this->currentCollege()->hasSystemMembership($systemId)) {
            $this->setActiveSystem($systemId);
        }

        return $this->redirect()->toRoute($redirect);
    }

    protected function getSubscription($year = null)
    {
        if ($year === null) {
            $year = $this->currentStudy()->getCurrentYear();
        }

        $subscription = $this->getSubscriptionModel()
            ->findOne($year, $this->currentCollege()->getId(), $this->currentStudy()->getId());

        return $subscription;
    }

    protected function downloadSubmittedValues($submittedValues, $year)
    {
        takeYourTime();

        $filename = 'submitted-values-' . $year;

        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $row = 1;

        // Format for header row
        $blueBar = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'DCE6F1')
            ),
            'font' => array(
                'bold' => true
            )
        );

        // Subheadings, just bold
        $bold = array(
            'font' => array(
                'bold' => true
            )
        );

        // Italics for computed benchmarks
        $italic = array(
            'font' => array(
                'italic' => true
            )
        );

        foreach ($submittedValues as $benchmarkGroup) {

            // Header
            $headerRow = array(
                $benchmarkGroup['benchmarkGroup']
            );

            $sheet->fromArray($headerRow, null, 'A' . $row);
            $sheet->getStyle("A$row:B$row")->applyFromArray($blueBar);
            $row++;

            // Data
            foreach ($benchmarkGroup['benchmarks'] as $benchmark) {
                // Is this a subheading?
                if (!empty($benchmark['heading'])) {
                    $dataRow = array(
                        $benchmark['name']
                    );

                    $sheet->fromArray($dataRow, null, 'A' . $row);
                    $sheet->getStyle("A$row:B$row")->applyFromArray($bold);
                    $row++;
                    continue;
                } else {
                    $b = $benchmark['benchmark'];
                }

                $dataRow = array(
                    $benchmark['benchmarkName'],
                    $benchmark['value']
                );

                if ($b->getComputed()) {
                    $sheet->getStyle("A$row")->applyFromArray($italic);
                }


                $sheet->fromArray($dataRow, null, 'A' . $row);
                $row++;
            }

            // Add a blank row after each form
            $row++;
        }

        // Align right
        $sheet->getStyle('B1:B500')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // Set column widths
        //PHPExcel_Shared_Font::setAutoSizeMethod(
        //    PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT
        //);
        foreach (range(0, 2) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        // redirect output to client browser
        header(
            'Content-Type: '.
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save('php://output');

        die;
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
