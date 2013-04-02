<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Debug\Debug;
use Mrss\Service\ImportNccbp;

class ImportController extends AbstractActionController
{

    public function indexAction()
    {
        // Should the DI/servicelocator be able to load these dependencies for me?
        $sm = $this->getServiceLocator();
        $nccbpDb = $sm->get('nccbp-db');
        $em = $sm->get('doctrine.entitymanager.orm_default');

        // Run the importer
        $importer = new ImportNccbp($nccbpDb, $em);
        $importer->importColleges();

        // Now find all the colleges
        // I want this ugly stuff tucked away under a nicer api,
        // like $something->findColleges() or $something->find()
        $colleges = $em->getRepository('Mrss\Entity\College')->findBy(
            array(),
            array('name' => 'ASC')
        );

        return array(
            'colleges' => $colleges
        );
    }
}
