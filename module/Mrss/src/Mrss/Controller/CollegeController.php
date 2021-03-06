<?php

namespace Mrss\Controller;

use Mrss\Entity\College;
use Mrss\Form\AbstractForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Cache\PatternFactory;
use Zend\View\Model\JsonModel;

class CollegeController extends BaseController
{
    public function indexAction()
    {
        $this->longRunningScript();

        // For completion heatmap
        $years = $this->getSubscriptionModel()
            ->getYearsWithSubscriptions($this->currentStudy());


        $cacheKey = 'admin--Colleges--' . $this->currentStudy()->getId();
        $outputCache = PatternFactory::factory('output', array(
            'storage' => 'filesystem',
            //'options' => array('ttl' => 3600)
        ));

        if (false || $this->params()->fromQuery('refresh')) {
            $outputCache->getOptions()->getStorage()->removeItem($cacheKey);
        }


        $success = null;
        $data = $outputCache->getOptions()->getStorage()->getItem($cacheKey, $success);
        if ($success) {
            $colleges = array(); // Won't get used
            //echo 'cached.';
        } else {
            //echo 'not cached';
            $colleges = $this->getCollegeModel()->findAll();
        }

        return array(
            'colleges' => $colleges,
            'years' => $years,
            'outputCache' => $outputCache,
            'cacheKey' => $cacheKey,
            'output' => $data
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
        $college = $this->getCollegeModel()->find($this->params('id'));

        // Handle invalid id
        if (empty($college)) {
            $this->flashMessenger()->addErrorMessage("Invalid college id.");
            return $this->redirect()->toUrl('/colleges');
        }

        return array(
            'studyConfig' => $this->getStudyConfig(),
            'college' => $college,
        );
    }

    public function deleteAction()
    {
        $college = $this->getCollegeModel()->find($this->params('id'));

        if ($college) {
            $this->getCollegeModel()->delete($college);
            $this->getCollegeModel()->getEntityManager()->flush();

            $this->flashMessenger()->addSuccessMessage('Deleted.');
        } else {
            $this->flashMessenger()->addErrorMessage('Failed to delete.');
        }

        return $this->redirect()->toRoute('colleges');
    }

    /**
     * This is very slow. Need to store the lat/lng of each college instead of
     * letting the map script look it up by address.
     *
     * @return array
     */
    public function mapAction()
    {
        return array('colleges' => $this->getCollegeModel()->findAll());
    }

    /**
     * Used for the autocomplete on the free signup page
     *
     * @return JsonModel
     */
    public function searchAction()
    {
        $term = $this->params()->fromQuery('term');

        $institutions = $this->getCollegeModel()->findByNameAndIdentifiers($term);
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

            $colleges = $this->getCollegeModel()->findByStudy($this->currentStudy());

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

    protected function getForm()
    {
        $form = new AbstractForm('college');

        $config = $this->getStudyConfig();

        $collegeFieldset = new \Mrss\Form\Fieldset\College(
            true,
            $config->include_canada,
            $config->institution_label
        );

        $collegeFieldset->setUseAsBaseFieldset(true);

        $collegeFieldset->setHydrator(
            new DoctrineHydrator($this->getEntityManager(), 'Mrss\Entity\College')
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

    protected function deleteCollegeCacheFile()
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

        /** @var \Mrss\Service\Import\College $service */

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
}
