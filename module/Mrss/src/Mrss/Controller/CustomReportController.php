<?php

namespace Mrss\Controller;

use Mrss\Entity\PeerGroup;
use Mrss\Form\Explore;
use Zend\Mvc\Controller\AbstractActionController;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Mrss\Form\Report as ReportForm;
use Mrss\Entity\Report;
use Mrss\Entity\ReportItem;
use Mrss\Entity\College;
use Mrss\Entity\User as User;
use Zend\View\Model\ViewModel;

class CustomReportController extends ReportController
{
    protected $public = false;
    //protected $peerGroupIdToCopy = 12650; // Peer group for sample reports
    //protected $peerGroupName = "Random Peer Group for Sample Report";

    protected $peerGroupIdToCopy = 14883; // Peer group for sample reports
    protected $peerGroupName = "2017 Missouri";

    /**
     * List a college's reports
     *
     * @return array
     */
    public function indexAction()
    {
        if ($redirect = $this->checkReportsAreOpen()) {
            return $redirect;
        }


        $webinarLink = '/webinar';
        if ($this->currentStudy()->getId() == 2) {
            $webinarLink = '/contact';
        } elseif ($this->currentStudy()->getId() == 3) {
            $webinarLink = '/free-webinar';
        }

        $currentUser = $this->zfcUserAuthentication()->getIdentity();

        return array(
            'webinarLink' => $webinarLink,
            'reports' => $this->getReportModel()
                    ->findByUserAndStudy($currentUser, $this->currentStudy())
        );
    }

    protected function getSystems()
    {
        /** @var \Mrss\Entity\College $currentCollege */
        $currentCollege = $this->currentCollege();

        return $currentCollege->getSystems();
    }

    public function addAction()
    {
        $form = new ReportForm($this->getSystems(), $this->getStudyConfig(), $this->getReportItemModel()->getEntityManager());

        $form->setHydrator(
            new DoctrineHydrator(
                $this->getServiceLocator()->get('em'),
                'Mrss\Entity\Report'
            )
        );

        $id = $this->params('id');
        if (empty($id) && $this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id');
        }
        $report = $this->getReport($id);

        $form->bind($report);

        //pr($form->get('permission')->getValue());

        if ($this->getRequest()->isPost()) {
            // Hand the POST data to the form for validation
            $data = $this->params()->fromPost();
            if (empty($data['permission'])) {
                $data['permission'] = 'private';
            }
            $form->setData($data);

            if ($form->isValid()) {
                $this->getReportModel()->save($report);
                $this->getServiceLocator()->get('em')->flush();

                $this->flashMessenger()->addSuccessMessage('Report saved.');

                return $this->redirect()->toRoute(
                    'reports/custom/build',
                    array('id' => $report->getId())
                );
            }

        }


        return array(
            'form' => $form,
            'report' => $report
        );
    }

    public function editAction()
    {
        return $this->addAction();
    }

    public function buildAction()
    {
        $id = $this->params()->fromRoute('id');
        $report = $this->getReport($id);
        $this->populateCache($report);

        return array(
            'report' => $report,
            'presentationOptions' => $this->getPresentationOptions()
        );
    }

    protected function getPresentationOptions()
    {
        $includeTrends = $this->getIncludeTrends();

        return Explore::getPresentationOptions($includeTrends);
    }

    public function getIncludeTrends()
    {
        $minYears = 3;

        $model = $this->getSubscriptionModel();
        $years = $model->getYearsWithSubscriptions($this->currentStudy());

        return (count($years) >= $minYears);
    }

    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');
        $print = $this->params()->fromRoute('print');
        $report = $this->getReport($id);
        $this->populateCache($report);

        $printMedia = 'print';
        if ($print) {
            $printMedia .= ',screen';

            // For the print version, show all labels
            $this->showAllLabels($report);
        }


        return array(
            'report' => $report,
            'print' => $print,
            'printMedia' => $printMedia
        );
    }

    /**
     * This action displays a custom report with no authentication, if it's marked public and config allows
     */
    public function publicViewAction()
    {
        $this->public = true;
        $allowPublic = $this->allowPublic();
        $id = $this->params()->fromRoute('id');
        $report = $this->getReport($id);

        if ($report && $allowPublic && $report->isPublic()) {
            $params = $this->viewAction();
            $params['public'] = true;
            $view = new ViewModel($params);
            $view->setTemplate('mrss/custom-report/view.phtml');
            return $view;
        } else {
            $this->flashMessenger()->addErrorMessage('Report not found.');
            return $this->redirect()->toUrl('/');
        }
    }

    protected function showAllLabels(Report $report)
    {
        foreach ($report->getItems() as $item) {
            $chart = $item->getCacheChart();

            if (empty($chart['chart'])) {
                continue;
            }

            if ($chart['chart']['type'] == 'column') {
                foreach ($chart['series'] as $key => &$series) {
                    foreach ($series['data'] as $dataKey => &$dataPoint) {
                        if (isset($dataPoint['dataLabels'])) {
                            $dataPoint['dataLabels']['enabled'] = true;
                        }
                    }
                }

            }

            if ($chart['chart']['type'] == 'line') {
                $chart['plotOptions']['line']['dataLabels']['enabled'] = true;
            }

            $item->setCacheChart($chart);
        }
    }

    public function deleteAction()
    {
        $id = $this->params('id');
        $report = $this->getReport($id);

        $name = $report->getName();

        $this->getReportModel()->delete($report);

        $this->flashMessenger()->addSuccessMessage('Report deleted:' . $name);
        return $this->redirect()->toRoute('reports/custom');
    }

    public function populateCache(Report $report)
    {
        $this->longRunningScript();

        /** @var \Mrss\Service\Report\CustomReportBuilder $reportBuilder */
        $reportBuilder = $this->getServiceLocator()->get('service.report.builder');

        $reportBuilder->build($report);
    }

    /**
     * @return \Mrss\Model\Report
     */
    protected function getReportModel()
    {
        return $this->getServiceLocator()->get('model.report');
    }

    /**
     * @return \Mrss\Model\ReportItem
     */
    protected function getReportItemModel()
    {
        return $this->getServiceLocator()->get('model.report.item');
    }

    public function allowPublic()
    {
        return $this->getStudyConfig()->allow_public_custom_report;
    }

    /**
     * @param $id
     * @throws \Exception
     * @return Report
     */
    public function getReport($id)
    {
        if (!empty($id)) {
            $report = $this->getReportModel()->find($id);
            $admin = $this->isAllowed('adminMenu', 'view');


            $public = ($report && $this->allowPublic() && $report->isPublic());

            if ((!$report && $this->public) || ($report && $this->public && !$report->isPublic())) {
                return null;
            }

            if ($report) {
                $college = $report->getUser()->getCollege();

                if (!$admin && !$public && $college->getId() != $this->currentCollege()->getId()) {
                    throw new \Exception('You cannot edit reports that do not belong to your college.');
                }

            }



        }

        $currentUser = $this->zfcUserAuthentication()->getIdentity();

        if (empty($report)) {
            $report = new Report;
            //$report->setCollege($this->currentCollege());
            $report->setUser($currentUser);
            $report->setCollege($currentUser->getCollege());
            $report->setStudy($this->currentStudy());
        }

        return $report;
    }

    public function clearCacheAction()
    {
        $studyId = $this->currentStudy()->getId();
        $this->getReportItemModel()->clearCache($studyId);
        $this->getSettingModel()->setValueForIdentifier('custom_report_cache_' . $studyId, date('c'));
        $this->getSettingModel()->getEntityManager()->flush();

        $this->flashMessenger()->addSuccessMessage("Cache cleared.");
        return $this->redirect()->toRoute('reports/custom/admin');
    }

    public function adminAction()
    {
        $studyId = $this->currentStudy()->getId();

        $cacheClearDate = $this->getSettingModel()->getValueForIdentifier('custom_report_cache_' . $studyId);

        $reportsNeedingCache = $this->getReportModel()->findByEmptyCache($studyId);

        $recentReports = $this->getReportModel()->findRecent($this->currentStudy(), 50);

        return array(
            'cacheClearDate' => $cacheClearDate,
            'reportsNeedingCache' => $reportsNeedingCache,
            'recentReports' => $recentReports
        );
    }

    public function rebuildCacheAction()
    {
        if ($id = $this->params()->fromRoute('id')) {
            $report = $this->getReport($id);
            $this->populateCache($report);
        }

        // Send back a simple response (no view file, no layout)
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent('ok');
        return $response;
    }

    protected $userIdsWhoAlreadyHaveIt = array();

    protected function prepareUsersWhoAlreadyHaveIt($sourceId)
    {
        foreach ($this->getReportModel()->findBySourceId($sourceId, $this->currentStudy()) as $report) {
            $this->userIdsWhoAlreadyHaveIt[] = $report->getUser()->getId();
        }
    }

    protected function userHasReport($user)
    {
        return in_array($user->getId(), $this->userIdsWhoAlreadyHaveIt);
    }

    public function copyAction()
    {
        $this->longRunningScript();
        $start = microtime(true);

        $id = $this->params()->fromRoute('id');
        $report = $this->getReport($id);

        $this->prepareUsersWhoAlreadyHaveIt($id);

        /** @var \Mrss\Model\Setting $settingsModel */
        $settingsModel = $this->getServiceLocator()->get('model.setting');


        // Force enable:
        //$settingsModel->setValueForIdentifier('copy_done', false);


        $copyDone = $settingsModel->getValueForIdentifier('copy_done');


        if (empty($copyDone)) {
            $count = 0;
            $duplicatesSkipped = 0;

            $year = $this->currentStudy()->getCurrentYear();

            // Limit to one state?
            $state = 'MO';

            if ($state) {
                $colleges = $this->getStateColleges($state);
            } else {
                $colleges = $this->getAllColleges($year);
            }


            // Test with JCCC
            //$college = $this->getCollegeModel()->find(101);
            //$colleges = array($college);

            foreach ($colleges as $college) {

                foreach ($college->getUsers() as $user) {
                    if (!$this->userHasReport($user)) {
                        $this->copyCustomReport($report, $user);
                        $count++;
                    } else {
                        $duplicatesSkipped++;
                    }


                    //pr("$count. {$college->getName()} {$user->getFullName()}");
                }
            }


            $elapsed = round(microtime(true) - $start);
            $this->flashMessenger()->addSuccessMessage(
                "Report copied to all users at all institutions ($count) in $elapsed seconds. $duplicatesSkipped duplicates skipped.
            Now <a href='/reports/custom/admin'>rebuild the cache</a>."
            );

            $settingsModel->setValueForIdentifier('copy_done', true);
        } else {
            $this->flashMessenger()->addErrorMessage('Copy already done.');
        }


        return $this->redirect()->toRoute('reports/custom');
    }

    protected function copyCustomReport(Report $sourceReport, $user)
    {
        // Get or create the sample peer group.
        if ($peerGroup = $this->getSamplePeerGroup($user)) {
            $peerGroupId = $peerGroup->getId();
        } else {
            //$peerGroupIdToCopy = null; // Peer group for sample reports
            $peerGroupId = $this->copyPeerGroup($this->peerGroupIdToCopy, $user);
        }


        $report = new Report;
        //$report->setCollege($college);
        $report->setUser($user);
        $report->setStudy($this->currentStudy());
        $report->setName($sourceReport->getName());
        $report->setDescription($sourceReport->getDescription());
        $report->setSourceReportId($sourceReport->getId());

        $this->getReportModel()->save($report);

        foreach ($sourceReport->getItems() as $reportItem) {
            $this->copyItem($reportItem, $report, $peerGroupId);
        }

        $this->getReportModel()->getEntityManager()->flush();
    }

    protected function getAllColleges($year = null)
    {
        $study = $this->currentStudy();

        if ($year) {
            $colleges = $this->getCollegeModel()->findByStudyAndYear($study, $year);
        } else {
            $colleges = $this->getCollegeModel()->findByStudy($study);
        }

        return $colleges;
    }

    protected function getStateColleges($state)
    {
        $colleges = $this->getCollegeModel()->findByState($state);

        return $colleges;
    }

    /** @return \Mrss\Model\College */
    protected function getCollegeModel()
    {
        return $this->getServiceLocator()->get('model.college');
    }

    protected function getSamplePeerGroup($user)
    {
        return $this->getPeerGroupModel()->findOneByUserAndName($user, $this->peerGroupName);
    }

    /**
     * @param $peerGroupIdToCopy
     * @param User $user
     * @return null
     * @internal param College $college
     */
    protected function copyPeerGroup($peerGroupIdToCopy, User $user)
    {
        $newPeerGroupId = null;
        if (!empty($peerGroupIdToCopy)) {
            $sampleGroup = $this->getPeerGroupModel()->find($peerGroupIdToCopy);
            $peers = $sampleGroup->getPeers();

            // Remove the owning college from the peer group
            if (($key = array_search($user->getCollege()->getId(), $peers)) !== false) {
                unset($peers[$key]);
            }

            // Create the new peer group
            $newPeerGroup = new PeerGroup();
            $newPeerGroup->setName($sampleGroup->getName());
            //$newPeerGroup->setCollege($college);
            $newPeerGroup->setUser($user);
            $newPeerGroup->setPeers($peers);
            $newPeerGroup->setStudy($this->currentStudy());

            $this->getPeerGroupModel()->save($newPeerGroup);
            $this->getPeerGroupModel()->getEntityManager()->flush();

            $newPeerGroupId = $newPeerGroup->getId();
        }

        return $newPeerGroupId;
    }

    /**
     * Strip out anything that shouldn't be copied.
     *
     * @param $sourceItem
     * @param $newReport
     * @param $peerGroupId
     */
    protected function copyItem(ReportItem $sourceItem, Report $newReport, $peerGroupId = null)
    {
        $config = $sourceItem->getConfig();
        $config['peerGroup'] = $peerGroupId;

        $item = new ReportItem();
        $item->setReport($newReport);
        $item->setName($sourceItem->getName());
        $item->setSequence($sourceItem->getSequence());
        $item->setType($sourceItem->getType());
        $item->setDescription($sourceItem->getDescription());
        $item->setYear($sourceItem->getYear());
        $item->setConfig($config);
        $item->setHighlightedCollege($sourceItem->getReport()->getCollege());

        $this->getReportItemModel()->save($item);
    }

    /**
     * @return \Mrss\Model\Setting
     */
    public function getSettingModel()
    {
        return $this->getServiceLocator()->get('model.setting');
    }
}
