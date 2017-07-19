<?php

namespace Mrss\Controller;

use Mrss\Entity\College;
use Mrss\Form\AbstractForm;
use Zend\Cache\StorageFactory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;
use Zend\Form\Form;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\View\Model\JsonModel;
use Zend\View\View;

class CollegeController extends AbstractActionController
{

    public function indexAction()
    {
        $this->longRunningScript();

        $Colleges = $this->getServiceLocator()->get('model.college');

        // For completion heatmap
        $subscriptionModel = $this->getServiceLocator()->get('model.subscription');
        $years = $subscriptionModel
            ->getYearsWithSubscriptions($this->currentStudy());


        return array(
            'colleges' => $Colleges->findAll(),
            'years' => $years
        );
    }


    public function downloadAction()
    {
        $service = $this->getServiceLocator()->get('download.colleges');
        $service->download();
    }

    public function flashtestAction()
    {
        $this->flashMessenger()->addMessage('Regular message.');
        $this->flashMessenger()->addSuccessMessage('Success!');
        $this->flashMessenger()->addErrorMessage('Error!');

        return $this->redirect()->toUrl('/colleges');
    }

    public function viewAction()
    {
        $Colleges = $this->getServiceLocator()->get('model.college');
        $college = $Colleges->find($this->params('id'));

        //$Studies = $this->getServiceLocator()->get('model.study');

        // Handle invalid id
        if (empty($college)) {
            $this->flashMessenger()->addErrorMessage("Invalid college id.");
            return $this->redirect()->toUrl('/colleges');
        }

        return array(
            'studyConfig' => $this->getStudyConfig(),
            'college' => $college,
            //'study' => $Studies->find(1)
        );
    }

    protected function getStudyConfig()
    {
        $studyConfig = $this->getServiceLocator()->get('study');

        return $studyConfig;
    }


    /**
     * This is very slow. Need to store the lat/lng of each college instead of
     * letting the map script look it up by address.
     *
     * @return array
     */
    public function mapAction()
    {
        $Colleges = $this->getServiceLocator()->get('model.college');

        return array('colleges' => $Colleges->findAll());
    }


    /**
     * Used for the autocomplete on the free signup page
     *
     * @return JsonModel
     */
    public function searchAction()
    {
        $term = $this->params()->fromQuery('term');

        /** @var \Mrss\Model\College $model */
        $model = $this->getServiceLocator()->get('model.college');

        $institutions = $model->findByNameAndIdentifiers($term);
        $json = array();
        foreach ($institutions as $institution) {
            $nameAndState = $institution->getName() . ' (' . $institution->getState() . ')';

            $json[] = array(
                'label' => $nameAndState,
                'value' => $nameAndState,
                'id' => $institution->getId()
            );
        }

        return new JsonModel($json);
    }

    /**
     * @return \Zend\Cache\Storage\Adapter\Filesystem
     */
    protected function getCache()
    {
        return $this->getServiceLocator()->get('cache');
    }

    public function peersAction()
    {
        $cacheKey = 'peer-map';
        $cache = $this->getCache();

        $result = $cache->getItem($cacheKey);

        if (!$result) {
            // Get a list of subscriptions to the current study for all years
            /** @var \Mrss\Model\College $collegeModel */
            $collegeModel = $this->getServiceLocator()->get('model.college');
            $studyId = $this->currentStudy()->getId();

            $colleges = $collegeModel->findByStudy($this->currentStudy());

            // Map markers
            $markers = array();
            $sectionIds = array();
            $collegeData = array();
            foreach ($colleges as $college) {
                $lat = $college->getLatitude();
                $lon = $college->getLongitude();

                if ($lat && $lon) {
                    $markers[] = array(
                        'latLng' => array($lat, $lon),
                        'name' => $college->getName()
                    );
                }

                $sectionIds[$college->getId()] = $college->getSectionIds();

                $system = null;
                if ($system = $college->getSystemNames()) {
                    $system = implode(', ', $system);
                    //pr($system);
                }

                if (is_array($system) && count($system) == 0) {
                    $system = '';
                }

                $collegeData[$college->getId()] = array(
                    'name' => $college->getName(),
                    'state' => $college->getState(),
                    'system' => $system
                );
            }


            $markers = json_encode($markers);

            $forCache = array(
                'colleges' => $collegeData,
                'markers' => $markers,
                'sectionIds' => $sectionIds
            );

            $cache->setItem($cacheKey, $forCache);
        } else {
            $collegeData = $result['colleges'];
            $markers = $result['markers'];
            $sectionIds = $result['sectionIds'];
        }

        return array(
            'colleges' => $collegeData,
            'markers' => $markers,
            'sections' => $this->currentStudy()->getSections(),
            'sectionIds' => $sectionIds
        );
    }

    public function getForm()
    {
        $em = $this->getServiceLocator()->get('em');

        $form = new AbstractForm('college');

        $config = $this->getStudyConfig();

        $collegeFieldset = new \Mrss\Form\Fieldset\College(
            true,
            $config->include_canada,
            $config->institution_label
        );

        $collegeFieldset->setUseAsBaseFieldset(true);

        $collegeFieldset->setHydrator(
            new DoctrineHydrator($em, 'Mrss\Entity\College')
        );

        $form->add($collegeFieldset);
        $form->add($form->getButtonFieldset());

        return $form;
    }

    public function editAction()
    {
        $collegeModel = $this->getCollegeModel();


        $form = $this->getForm();

        $collegeId = $this->params()->fromRoute('id');
        if (empty($collegeId)) {
            if ($institution = $this->params()->fromPost('institution')) {
                $collegeId = $institution['id'];
            }
        }


        $isAdmin = $this->isAllowed('adminMenu', 'view');

        if (empty($collegeId)) {
            /** @var \Mrss\Entity\College $college */
            $college = $this->currentCollege();
        } else {
            $college = $collegeModel->find($collegeId);
        }

        $redirect = $this->params()->fromRoute('redirect');

        $form->bind($college);

        // Redirect to renew if needed
        $form->addRedirect($redirect);

        // Process the form
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $collegeModel->save($college);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Institution saved.');

                // Get the redirect
                $data = $this->params()->fromPost();

                if (!empty($isAdmin)) {
                    $redirect = $this->redirect()
                        ->toUrl('/colleges/view/' . $college->getId());
                } elseif (!empty($data['redirect'])) {
                    $redirect = $this->redirect()->toUrl('/' . $data['redirect']);
                } else {
                    $redirect = $this->redirect()->toUrl('/members');
                }



                return $redirect;
            }

        }


        return array(
            'form' => $form,
            'isAdmin' => $isAdmin
        );
    }

    public function addAction()
    {
        $form = $this->getForm();

        $college = new College();

        $form->bind($college);

        // Process the form
        if ($this->getRequest()->isPost()) {

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $this->getCollegeModel()->save($college);
                $this->getServiceLocator()->get('em')->flush();

                $this->deleteCollegeCacheFile();

                $this->flashMessenger()->addSuccessMessage('Institution saved.');

                // Get the redirect
                return $this->redirect()->toUrl('/colleges');
            }

        }

        return array(
            'form' => $form,
        );
    }

    public function deleteCollegeCacheFile()
    {
        unlink('public/files/all-colleges.json');
    }

    public function usersAction()
    {
        $college = $this->currentCollege();

        return array(
            'college' => $college,
            'redirect' => $this->params()->fromRoute('redirect')
        );
    }

    public function importAction()
    {
        $altService = $this->params()->fromRoute('service');

        $redirectRoute = '/colleges/import';
        if (!empty($altService)) {
            if ($altService == 'demo') {
                $service = $this->getServiceLocator()->get('service.import.colleges.demo');
                $redirectRoute = '/colleges/import-demo';
            } elseif ($altService == 'category') {
                $service = $this->getServiceLocator()->get('service.import.colleges.category');
                $redirectRoute = '/colleges/import-demo/category';
            }
        } else {
            $service = $this->getServiceLocator()->get('service.import.colleges');
        }

        $form = $service->getForm();

        // Handle the form
        /** @var \Zend\Http\PhpEnvironment\Request $request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->longRunningScript();

            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $form->setData($post);
            if ($form->isValid()) {
                $data = $form->getData();
                $filename = $data['file']['tmp_name'];

                $stats = $service->import($filename);

                $this->flashMessenger()->addSuccessMessage($stats);
                return $this->redirect()->toUrl($redirectRoute);
            }
        }

        return array(
            'form' => $form
        );
    }

    public function cacheCollegesAction()
    {
        $cacheFile = '/public/files/all-colleges.json';
        $json = $this->getAllCollegesAsJson();

        $path = getcwd() . $cacheFile;

        // Write to the cache
        file_put_contents($path, $json);

        // Send the file to the requestor
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent($json);

        return $response;

    }

    /**
     * @return \Mrss\Model\College
     */
    protected function getCollegeModel()
    {
        $collegeModel = $this->getServiceLocator()->get('model.college');

        return $collegeModel;
    }

    protected function getAllCollegesAsJson()
    {
        $collegeModel = $this->getCollegeModel();
        $colleges = $collegeModel->findAll();

        $allColleges = array();
        foreach ($colleges as $college) {
            $nameAndState = $college->getName() . ' (' . $college->getState() . ')';
            if ($ipeds = $college->getIpeds()) {
                $nameAndState .= ', IPEDS: ' . $ipeds;
            }
            if ($opeId = $college->getOpeId()) {
                $nameAndState .= ', OPE: ' . $opeId;
            }
            $allColleges[] = array(
                'label' => $nameAndState,
                'id' => $college->getId()
            );
        }

        return json_encode($allColleges);
    }


    protected function longRunningScript()
    {
        takeYourTime();

        // Turn off query logging
        $this->getServiceLocator()
            ->get('em')
            ->getConnection()
            ->getConfiguration()
            ->setSQLLogger(null);
    }
}
