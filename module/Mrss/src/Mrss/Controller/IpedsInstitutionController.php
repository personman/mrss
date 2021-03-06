<?php

namespace Mrss\Controller;

use Mrss\Entity\IpedsInstitution;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Debug\Debug;

class IpedsInstitutionController extends AbstractActionController
{

    public function indexAction()
    {
        $Colleges = $this->getServiceLocator()->get('model.college');

        return array(
            'colleges' => $Colleges->findAll()
        );
    }

    public function searchAction()
    {
        $term = $this->params()->fromQuery('term');
        $model = $this->getServiceLocator()->get('model.ipeds.institution');

        $institutions = $model->searchByName($term);
        $json = array();
        foreach ($institutions as $institution) {
            $json[] = array(
                'label' => $institution->getName(),
                'value' => $institution->getName(),
                'ipeds' => $institution->getIpeds(),
                'address' => $institution->getAddress(),
                'city' => $institution->getCity(),
                'state' => $institution->getState(),
                'zip' => $institution->getZip()
            );
        }
        //var_dump($institutions);


        return new JsonModel($json);
    }

    /**
     * Import from csv
     */
    public function importAction()
    {
        $file = 'data/imports/ipeds_institutions.csv';
        $model = $this->getServiceLocator()->get('model.ipeds.institution');
        $collegeModel = $this->getServiceLocator()->get('model.college');

        if (($handle = fopen($file, "r")) !== false) {
            $headerSkipped = false;
            $count = 0;

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                if (!$headerSkipped) {
                    $headerSkipped = true;
                    continue;
                }

                $ipeds = $data[0];
                $name = $data[1];
                $city = $data[2];
                $state = $data[3];

                // Check for a duplicate
                $institution = $model->findOneByIpeds($ipeds);
                $college = $collegeModel->findOneByIpeds($ipeds);

                if (!$institution) {
                    $institution = new IpedsInstitution();
                }

                $institution->setIpeds($ipeds);
                $institution->setName($name);
                $institution->setCity($city);
                $institution->setState($state);

                // Load the address and zip from our subscribers, if possible
                if ($college) {
                    $institution->setAddress($college->getAddress());
                    $institution->setZip($college->getZip());
                }

                $model->save($institution);
                $count++;
            }

            $this->getServiceLocator()->get('em')->flush();

            fclose($handle);

            $this->flashMessenger()->addSuccessMessage(
                "$count institutions imported."
            );
        } else {
            $this->flashMessenger()->addErrorMessage(
                'Import failed. Make sure the file exists.'
            );
        }

        return $this->redirect()->toUrl('/colleges');
    }
}
