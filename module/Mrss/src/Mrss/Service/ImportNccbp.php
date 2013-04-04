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

    /**
     * @var \Mrss\Model\College
     */
    protected $collegeModel;

    /**
     * @var array
     */
    protected $stats = array('imported' => 0, 'skipped' => 0);

    /**
     * Constructor
     *
     * @param \Zend\Db\Adapter $dbAdapter
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(
        \Zend\Db\Adapter $dbAdapter,
        \Doctrine\ORM\EntityManager $entityManager
    ) {
        $this->dbAdapter = $dbAdapter;
        $this->entityManager = $entityManager;
    }

    /**
     * Import colleges from NCCBP
     *
     * Connect to the db with Zend_Db, query for colleges, check for dupliates,
     * make an entity out of each college, save
     */
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
            $existingCollege = $this->getCollegeModel()->findOneByIpeds($ipeds);

            // This class will need to know very little about
            // Doctrine ORM. We do still have the flush() call below.

            if (!empty($existingCollege)) {
                // Skip this college as we've already imported it
                $this->stats['skipped']++;

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

            $this->getCollegeModel()->save($college);

            $this->stats['imported']++;
        }

        $this->entityManager->flush();
    }

    /**
     * Import Observations
     */
    public function importObservations()
    {

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

    public function getStats()
    {
        return $this->stats;
    }

    public function setCollegeModel($model)
    {
        $this->collegeModel = $model;

        return $this;
    }

    protected function getCollegeModel()
    {
        return $this->collegeModel;
    }
}
