<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;
use Mrss\Service;

class ImportController extends AbstractActionController
{

    public function indexAction()
    {
        $sm = $this->getServiceLocator();
        $nccbpDb = $sm->get('nccbp-db');

        $importer = new ImportNccbp();

        Debug::dump($importer);die;
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
