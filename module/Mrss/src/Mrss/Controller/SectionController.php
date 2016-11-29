<?php

namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class SectionController extends AbstractActionController
{
    /**
     * @var \Mrss\Model\Study
     */
    protected $studyModel;

    public function indexAction()
    {
        $studyId = $this->params()->fromRoute('study');

        return array(
        );
    }
}
