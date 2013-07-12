<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class ObservationController extends AbstractActionController
{
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

    public function getCurrentObservation()
    {
        // Find the observation by the year and the user's college
        /** @var \Mrss\Entity\User $user */
        $user = $this->zfcUserAuthentication()->getIdentity();
        $collegeId = $user->getCollege()->getId();

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

                $filename = $data['file']['tmp_name'];
                $excelService = new \Mrss\Service\Excel();
                $data = $excelService->getObservationDataFromExcel($filename);

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
                        $observation->set($column, $value);
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
        $user = $this->zfcUserAuthentication()->getIdentity();
        $collegeId = $user->getCollege()->getId();

        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $subscription = $subscriptionModel->findCurrentSubscription(
            $this->currentStudy(),
            $collegeId
        );

        //var_dump($this->currentStudy());
        //var_dump($subscription); die;

        $excelService = new \Mrss\Service\Excel();
        $excelService->getExcelForSubscription($subscription);

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
}
