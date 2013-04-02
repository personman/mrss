<?php

namespace Mrss\Service;

use Zend\Debug\Debug;
use Mrss\Entity\College;
use Mrss\Model;

/**
 * Import data from NCCBP database
 *
 * Get nccbp db, query, translate to entity, save
 */
class ImportNccbp
{
    /**
     * The nccbp db using zend db
     * @var
     */
    protected $dbAdapter;

    /**
     * The mrss doctrine entity manager
     * @var
     */
    protected $entityManager;


    public function __construct($dbAdapter, $entityManager)
    {
        $this->dbAdapter = $dbAdapter;
        $this->entityManager = $entityManager;
    }

    public function import()
    {
        $query = $this->getTestQuery();
        $statement = $this->dbAdapter->query($query);
        $result = $statement->execute();

        foreach ($result as $row) {
            // Does the college already exist in mrss?
            //if (!$this->)
            Debug::dump($row, 'Row');
        }

        Debug::dump($result, 'Result');
    }


    public function importColleges()
    {
        $query = "select g.title, i.*
from content_type_group_subs_info i
inner join node n on n.nid = i.nid
inner join og_ancestry a on n.nid = a.nid
inner join node g on a.group_nid = g.nid";

        $statement = $this->dbAdapter->query($query);
        $result = $statement->execute();

        foreach ($result as $row) {
            $ipeds = $this->padIpeds($row['field_ipeds_id_value']);

            // Does this college already exist?
            // Good:
            /*$existingCollege = $this->entityManager
                ->getRepository('Mrss\Entity\College')
                ->findOneBy(array('ipeds' => $ipeds));*/

            // Better:
            $Colleges = new \Mrss\Model\College();
            $Colleges->setEntityManager($this->entityManager);
            $existingCollege = $Colleges->findOneByIpeds($ipeds);

            // Best:
            // @todo: move this to service locator
            // Once that's done, this class will need to know very little about
            // Doctrine ORM. We do still have the persist() call below.



            if (!empty($existingCollege)) {
                // Skip this college as we've already imported it
                continue;
            }

            // Populate the college
            $college = new College;

            // College name
            $college->setName($row['field_institution_name_value']);

            // Ipeds
            $college->setIpeds($ipeds);

            // Address
            $college->setAddress($row['field_address_value']);
            $college->setCity($row['field_city_value']);
            $college->setState($row['field_state_value']);
            $college->setZip($row['field_zip_code_value']);

            $Colleges->save($college);
        }

        $this->entityManager->flush();
    }


    /**
     * Convert from nccbp field name to mrss field name
     *
     * @param $fieldName
     * @return string
     * @throws \Exception
     */
    public function convertFieldName($fieldName)
    {
        // This takes the format 'field_18_tot_fte_fin_aid_staff_value'
        // and converts it to this: 'tot_fte_fin_aid_staff'
        preg_match('/^field_(.\d)_(.*)_value$/', $fieldName, $matches);

        if (empty($matches[2])) {
            throw new \Exception("'$fieldName' is not a valid field.");
        }

        $converted = $matches[2];

        return $converted;
    }

    /**
     * An IPEDS is a 6-digit number. Pad with leading zeroes if needed.
     *
     * @param string $ipeds
     * @return string
     */
    public function padIpeds($ipeds)
    {
        $ipeds = str_pad($ipeds, 6, '0', STR_PAD_LEFT);

        return $ipeds;
    }

    protected function getTestQuery()
    {
        $query = "select n.title, y.field_data_entry_year_value as year, sss.*
from content_type_group_form18_stud_serv_staff sss
inner join node n on n.nid = sss.nid
inner join content_field_data_entry_year y on y.nid = n.nid
where field_18_stud_act_staff_ratio_value is not null";

        return $query;
    }
}
