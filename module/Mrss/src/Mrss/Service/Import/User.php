<?php

namespace Mrss\Service\Import;

use Mrss\Entity\System;
use Mrss\Service\Import;
use PHPExcel_Worksheet_Row;
use Mrss\Entity\College as CollegeEntity;
use Mrss\Entity\User as UserEntity;

class User extends Import
{
    /** @var \Mrss\Model\College $collegeModel */
    protected $collegeModel;

    /** @var \Mrss\Model\User $userModel */
    protected $userModel;

    /** @var \Mrss\Model\Study $study */
    protected $study;


    protected function saveRow(PHPExcel_Worksheet_Row $row)
    {

        $data = $this->getDataFromRow($row);

        if (empty($data['email-1'])) {
            return false;
        }

        if ($college = $this->getCollegeModel()->findOneByIpeds($data['ipeds'])) {
            $this->saveUsers($college, $data);
        } else {
            $this->addMessage("No college fount for " . $data['ipeds'] . ". Email: " . $data['email-1'] . '. ');
        }
    }

    protected function saveUsers(CollegeEntity $college, $data)
    {
        $userNumbers = range(1, 5);
        foreach ($userNumbers as $userNumber) {
            $email = $data['email-' . $userNumber];
            $firstName = $data['firstName-' . $userNumber];
            $lastName = $data['lastName-' . $userNumber];

            if ($email) {
                // Does the user exist?
                if ($existing = $this->getUserModel()->findOneByEmail($email)) {
                    // Do nothing. They're already here.
                } else {
                    // Create a new user
                    $user = new UserEntity();
                    $user->setEmail($email);
                    $user->setFirstName($firstName);
                    $user->setLastName($lastName);
                    $user->setCollege($college);
                    $user->addStudy($this->getStudy());
                    $user->setState(1);

                    // Role
                    $role =  'viewer';
                    if ($userNumber == 1) {
                        $role = 'data';
                    }
                    $user->setRole($role);

                    // Placeholder encrypted password for new users
                    $pass = '$2y$10$110LLMtUracaSOMEl4gfIuduZul57iLcPxQ8.6vKBCFKFzUHLFagm';
                    $user->setPassword($pass);


                    $this->getUserModel()->save($user);
                }
            }
        }
    }

    protected function getDataFromRow(PHPExcel_Worksheet_Row $row)
    {
        $rowIndex = $row->getRowIndex();

        $rowData = array();
        foreach ($this->getMap() as $property => $column) {
            $rowData[$property] = $this->excel->getActiveSheet()->getCellByColumnAndRow($column, $rowIndex)->getValue();
        }

        return $rowData;
    }

    /**
     * Maps excel columns to College entity property names
     * @return array
     */
    protected function getMap()
    {
        return array(
            'email-1' => 0,
            'ipeds' => 1,
            'firstName-1' => 2,
            'lastName-1' => 3,
            'firstName-2' => 4,
            'lastName-2' => 5,
            'email-2' => 6,
            'firstName-3' => 7,
            'lastName-3' => 8,
            'email-3' => 9,
            'firstName-4' => 10,
            'lastName-4' => 11,
            'email-4' => 12,
            'firstName-5' => 13,
            'lastName-5' => 14,
            'email-5' => 15,
        );
    }

    public function getEntity($rowData)
    {
        // First, see if there's a matching entity in the db already
        $entity = $this->getCollegeModel()->findOneByOpeId($rowData['opeId']);

        // If not, create a blank one
        if (empty($entity)) {
            $entity = new CollegeEntity();
        }

        // Now plug in the data
        $entity->setOpeId($rowData['opeId']);
        $entity->setIpeds($rowData['ipeds']);
        $entity->setName($rowData['name']);
        $entity->setAddress($rowData['address']);
        $entity->setCity($rowData['city']);
        $entity->setState($rowData['state']);
        $entity->setZip($rowData['zip']);

        return $entity;
    }

    public function setCollegeModel($collegeModel)
    {
        $this->collegeModel = $collegeModel;

        return $this;
    }

    public function getCollegeModel()
    {
        return $this->collegeModel;
    }

    public function setUserModel($userModel)
    {
        $this->userModel = $userModel;

        return $this;
    }

    public function getUserModel()
    {
        return $this->userModel;
    }

    protected function flush()
    {
        $this->getCollegeModel()->getEntityManager()->flush();
    }

    /**
     * @return \Mrss\Entity\Study
     */
    public function getStudy()
    {
        return $this->study;
    }

    /**
     * @param \Mrss\Entity\Study $study
     */
    public function setStudy($study)
    {
        $this->study = $study;
    }
}
