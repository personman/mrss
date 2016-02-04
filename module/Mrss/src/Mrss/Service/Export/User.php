<?php

namespace Mrss\Service\Export;

use Mrss\Service\Export;
use Mrss\Entity\User as UserEntity;
use PHPExcel;

class User extends Export
{
    /**
     * @param UserEntity[] $users
     * @throws \PHPExcel_Exception
     */
    public function export($users)
    {
        $startingCell = 'A1';
        $filename = 'users-with-data-issues.xlsx';

        // Start with the headers
        $userData = array(array('E-mail', 'Prefix', 'First Name', 'Last Name', 'Title', 'Institution'));

        foreach ($users as $user) {
            $userData[] = array(
                $user->getEmail(),
                $user->getPrefix(),
                $user->getFirstName(),
                $user->getLastName(),
                $user->getTitle(),
                $user->getCollege()->getName()
            );
        }

        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();

        $sheet->fromArray($userData, null, $startingCell);

        foreach (range(0, 5) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }

        // redirect output to client browser
        $this->downloadExcel($excel, $filename);
    }
}
