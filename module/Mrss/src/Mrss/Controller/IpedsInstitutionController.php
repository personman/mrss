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
        $model = $this->getServiceLocator()->get('model.ipedsInstitution');

        $institutions = $model->searchByName($term);
        $json = array();
        foreach ($institutions as $institution) {
            $json[] = array(
                'label' => $institution->getName(),
                'value' => $institution->getName(),
                'ipeds' => $institution->getIpeds(),
                'city' => $institution->getCity(),
                'state' => $institution->getState()
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
        $model = $this->getServiceLocator()->get('model.ipedsInstitution');

        if (($handle = fopen($file, "r")) !== FALSE) {
            $headerSkipped = false;
            $count = 0;

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (!$headerSkipped) {
                    $headerSkipped = true;
                    continue;
                }

                $ipeds = $data[0];
                $name = $data[1];
                $city = $data[2];
                $state = $data[3];

                // Check for a duplicate
                $dupe = $model->findOneByIpeds($ipeds);

                if (!$dupe) {
                    $institution = new IpedsInstitution();
                    $institution->setIpeds($ipeds);
                    $institution->setName($name);
                    $institution->setCity($city);
                    $institution->setState($state);

                    $model->save($institution);
                    $count++;
                }
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
