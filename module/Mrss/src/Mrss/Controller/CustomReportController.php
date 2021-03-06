<?php

namespace Mrss\Controller;

use Mrss\Entity\PeerGroup;
use Mrss\Form\Explore;
use Mrss\Form\PublishCustomReport;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Mrss\Form\Report as ReportForm;
use Mrss\Entity\Report;
use Mrss\Entity\College;
use Mrss\Entity\ReportItem;
use Mrss\Entity\User as User;
use Zend\View\Model\ViewModel;

class CustomReportController extends ReportController
{
    protected $public = false;
    //protected $peerGroupIdToCopy = 12650; // Peer group for sample reports
    //protected $peerGroupName = "Random Peer Group for Sample Report";

    protected $peerGroupIdToCopy; // Peer group for sample reports
    //protected $peerGroupName = "2017 Missouri";

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

        $currentUser = $this->getCurrentUser();

        $reports = $this->getReportModel()
            ->findByUserAndStudy($currentUser, $this->currentStudy(), $this->getImpersonatedCollege());

        return array(
            'webinarLink' => $webinarLink,
            'reports' => $reports
        );
    }

    /**
     * Is a system admin impersonating another college?
     *
     * @return \Mrss\Entity\College|null
     */
    protected function getImpersonatedCollege()
    {
        $currentUser = $this->getCurrentUser();
        $currentCollege = $this->currentCollege();

        $impersonatedCollege = null;
        if ($currentUser->getCollege()->getId() != $currentCollege->getId()) {
            $impersonatedCollege = $currentCollege;
        }

        return $impersonatedCollege;
    }

    protected function getSystems()
    {
        /** @var \Mrss\Entity\College $currentCollege */
        $currentCollege = $this->currentCollege();

        return $currentCollege->getSystems();
    }

    public function addAction()
    {
        $form = new ReportForm(
            $this->getSystems(),
            $this->getStudyConfig(),
            $this->getEntityManager()
        );

        $form->setHydrator(
            new DoctrineHydrator(
                $this->getEntityManager(),
                'Mrss\Entity\Report'
            )
        );

        $reportId = $this->params('id');
        if (empty($reportId) && $this->getRequest()->isPost()) {
            $reportId = $this->params()->fromPost('id');
        }
        $report = $this->getReport($reportId);

        $form->bind($report);

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
        $reportId = $this->params()->fromRoute('id');
        $report = $this->getReport($reportId);
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
        $reportId = $this->params()->fromRoute('id');
        $print = $this->params()->fromRoute('print');
        $embed = $this->params()->fromRoute('embed');
        $report = $this->getReport($reportId);
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
            'printMedia' => $printMedia,
            'embed' => $embed,
            'systemLogo' => $this->getSystemLogo($report)
        );
    }

    /**
     * @param Report $report
     * @return string
     */
    protected function getSystemLogo($report)
    {
        $logo = null;

        if ($system = $report->getSystem()) {
            $systemId = $system->getId();

            $logos = $this->getStudyConfig()->system_logos;

            if (!empty($logos[$systemId])) {
                $logo = $logos[$systemId];
            }
        }

        return $logo;
    }

    /**
     * This action displays a custom report with no authentication, if it's marked public and config allows
     */
    public function publicViewAction()
    {
        $this->public = true;
        $allowPublic = $this->allowPublic();
        $reportId = $this->params()->fromRoute('id');
        $report = $this->getReport($reportId);

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
                foreach ($chart['series'] as &$series) {
                    foreach ($series['data'] as &$dataPoint) {
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
        $reportId = $this->params('id');
        $report = $this->getReport($reportId);

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
     * @param $reportId
     * @throws \Exception
     * @return Report
     */
    public function getReport($reportId)
    {
        if (!empty($reportId)) {
            $report = $this->getReportModel()->find($reportId);
            $admin = $this->isAllowed('adminMenu', 'view');


            $public = ($report && $this->allowPublic() && $report->isPublic());

            if ((!$report && $this->public) || ($report && $this->public && !$report->isPublic())) {
                return null;
            }

            if ($report) {
                $college = $report->getUser()->getCollege();

                if (!$admin && !$public && $college->getId() != $this->currentCollege()->getId()) {
                    // Are they a system admin editing a report they created while impersonating?
                    $college = $report->getCollege();
                    $impersonatedCollege = $this->getImpersonatedCollege();
                    if (!$impersonatedCollege || $this->getCurrentUser()->administersSystem($this->getActiveSystem())) {
                        throw new \Exception('You cannot edit reports that do not belong to your college.');
                    }


                }
            }
        }

        $currentUser = $this->getCurrentUser();

        if (empty($report)) {
            $report = new Report;
            $report->setUser($currentUser);
            $report->setCollege($this->getImpersonatedCollege());
            $report->setStudy($this->currentStudy());
        }

        $year = $this->getCurrentYear();
        $system = $this->getStudyConfig()->use_structures;
        $updated = $this->getPercentileService()->getReportCalculatedSetting($year, $system, false);
        $report->setUpdated($updated);

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
        if ($reportId = $this->params()->fromRoute('id')) {
            $report = $this->getReport($reportId);
            $this->populateCache($report);
        }

        // Send back a simple response (no view file, no layout)
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->setContent('ok');
        return $response;
    }

    protected $userIdsWhoHaveIt = array();

    protected function prepareUsersWhoAlreadyHaveIt($sourceId)
    {
        foreach ($this->getReportModel()->findBySourceId($sourceId, $this->currentStudy()) as $report) {
            $this->userIdsWhoHaveIt[$report->getUser()->getId()] = $report;
        }
    }

    protected function userHasReport(User $user)
    {
        $userIds = array_keys($this->userIdsWhoHaveIt);
        return in_array($user->getId(), $userIds);
    }

    protected function getReportToUpdate($userId)
    {
        $report = null;

        if (!empty($this->userIdsWhoHaveIt[$userId])) {
            $report = $this->userIdsWhoHaveIt[$userId];
        }

        return $report;
    }

    public function publishAction()
    {
        $form = $this->getPublishForm();
        $reportId = $this->params()->fromRoute('id');

        if (!$this->canPublish($reportId)) {
            $this->flashMessenger()->addErrorMessage("I'm sorry, Dave. I'm afraid I can't do that.");
            return $this->redirect()->toUrl('/reports/custom');
        }

        $report = $this->getReportModel()->find($reportId);

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->getData();
                $this->peerGroupIdToCopy = $reportId;

                return $this->copyReport($report, $data['target'], $data['group'], $data['addThisCollege']);
            }
        }

        return array(
            'form' => $form,
            'report' => $report
        );
    }

    public function duplicateAction()
    {
        $reportId = $this->params()->fromRoute('id');
        $newReportId = null;
        if ($sourceReport = $this->getReportModel()->find($reportId)) {
            if ($newReportId = $this->copyCustomReport($sourceReport, $this->getCurrentUser(), false, true)) {
                $message = "Your report has been duplicated. Please rename it below.";
            }
        }

        if (!empty($message)) {
            $this->flashMessenger()->addSuccessMessage($message);
            return $this->redirect()->toRoute('reports/custom/edit', array('id' => $newReportId));
        } else {
            $this->flashMessenger()->addErrorMessage('Problem duplicating report.');
            return $this->redirect()->toRoute('reports/custom');
        }
    }

    /**
     * Can the current user publish the report?
     *
     * @param $reportId
     * @return bool
     */
    protected function canPublish($reportId)
    {
        $can = false;
        if ($this->getCurrentUser()->isAdmin()) {
            $can = true;
        } elseif ($this->getCurrentUser()->isSystemAdmin()) {
            $can = true;
        }/* elseif ($this->userOwnsReport($reportId)) {
            $can = true;
        }*/

        return $can;
    }

    /**
     * @param Report $report
     * @return bool
     */
    protected function userOwnsReport($report)
    {
        return ($report->getUser()->getId() == $this->getCurrentUser()->getId());
    }

    protected function getPublishForm()
    {
        $form = new PublishCustomReport($this->getCurrentUser());

        return $form;
    }

    public function copyReport(Report $report, $targetPeerGroupId, $peerGroup, $addThisCollege = false)
    {
        $this->longRunningScript();
        $start = microtime(true);

        $this->prepareUsersWhoAlreadyHaveIt($report->getId());
        $this->peerGroupIdToCopy = $peerGroup;

        if (true) {
            $count = 0;
            $duplicatesSkipped = 0;

            //$year = $this->currentStudy()->getCurrentYear();

            $colleges = $this->getTargetColleges($targetPeerGroupId);
            foreach ($colleges as $college) {
                foreach ($college->getUsers() as $user) {
                    // Skip yourself.
                    if ($user->getId() == $this->getCurrentUser()->getId()) {
                        continue;
                    }

                    if (true) {
                        $this->copyCustomReport($report, $user, $addThisCollege);
                        $count++;
                    } else {
                        $duplicatesSkipped++;
                    }
                }
            }


            $elapsed = round(microtime(true) - $start);
            $message = "Report copied to all users at peer group members ($count) in $elapsed seconds. ";
            if ($this->getCurrentUser()->isAdmin()) {
                $message .= "Now <a href='/reports/custom/admin'>rebuild the cache</a>.";
            }

            $this->flashMessenger()->addSuccessMessage($message);
        } else {
            $this->flashMessenger()->addErrorMessage('Copy already done.');
        }

        //die('debugging');

        return $this->redirect()->toRoute('reports/custom');
    }

    protected function getTargetColleges($peerGroupId)
    {
        $peerGroup = $this->getPeerGroupModel()->find($peerGroupId);

        $collegeIds = $peerGroup->getPeers();

        // Make sure system admins don't copy the report outside of their system/network
        if ($this->getCurrentUser()->isSystemAdmin()) {
            $systems = $this->getCurrentUser()->getSystemsAdministered(true);
            $memberIds = array();
            foreach ($systems as $system) {
                $system = $this->getSystemModel()->find($system);

                foreach ($system->getColleges() as $memberCollege) {
                    $memberIds[] = $memberCollege->getId();
                }
            }

            $newIds = array();
            foreach ($collegeIds as $collegeId) {
                if (in_array($collegeId, $memberIds)) {
                    $newIds[] = $collegeId;
                }
            }
            $collegeIds = $newIds;
        }

        $colleges = $this->getCollegeModel()->findByIds($collegeIds);

        // Add their own college, too, so they can copy it to colleagues.
        $colleges[] = $this->currentCollege();

        return $colleges;


        // Limit to one state?
        /*$state = 'MO';

        if ($state) {
            $colleges = $this->getStateColleges($state);
        } else {
            $colleges = $this->getAllColleges($year);
        }
        */
    }

    protected function addThisCollegeToPeerGroup(PeerGroup $peerGroup)
    {
        $peers = $peerGroup->getPeers();
        $sampleGroup = $this->getPeerGroupModel()->find($this->peerGroupIdToCopy);

        $sourceCollege = $sampleGroup->getCollege();
        if (empty($sourceCollege)) {
            $sourceCollege = $sampleGroup->getUser()->getCollege();
        }

        if (!empty($sourceCollege)) {
            $sourceCollegeId = $sourceCollege->getId();

            if (!in_array($sourceCollege, $peers)) {
                $peers[] = $sourceCollegeId;
            }
        }

        $peerGroup->setPeers($peers);
        $this->getPeerGroupModel()->save($peerGroup);
        $this->getPeerGroupModel()->getEntityManager()->flush();

        return $peerGroup;
    }

    /**
     * @param Report $sourceReport
     * @param User $user
     * @return bool
     */
    protected function copyCustomReport(Report $sourceReport, $user, $addThisCollege = false, $duplicate = false)
    {
        // Don't copy to yourself
        if (!$duplicate && $sourceReport->getUser()->getId() == $user->getId()) {
            return false;
        }

        // If duplicating, use the same peer group id
        if ($duplicate) {
            $peerGroupId = 'same';
        } else {
            // If not duplicating, get or create the sample peer group.
            if ($peerGroup = $this->getSamplePeerGroup($user)) {
                if ($addThisCollege) {
                    $peerGroup = $this->addThisCollegeToPeerGroup($peerGroup);
                }
                $peerGroupId = $peerGroup->getId();
            } else {
                //$peerGroupIdToCopy = null; // Peer group for sample reports
                $peerGroupId = $this->copyPeerGroup($this->peerGroupIdToCopy, $user, $addThisCollege);
            }
        }

        $report = $this->getOrCreateReport($user);
        //$report->setCollege($user->getCollege());

        $newName = $sourceReport->getName();
        if ($duplicate) {
            $newName .= ' copy';
        }

        $report->setName($newName);
        $report->setDescription($sourceReport->getDescription());
        $report->setDisplayFootnotes($sourceReport->getDisplayFootnotes());
        $report->setSourceReportId($sourceReport->getId());
        $report->setPermission('private');

        $this->getReportModel()->save($report);

        /*pr($user->getEmail());
        pr($sourceReport->getDescription());
        pr($report->getId());
        */

        // Old ones we might delete (if the source item was deleted)
        $oldItems = array();
        foreach ($report->getItems() as $oldItem) {
            if ($oldItem->getSourceItemId()) {
                $oldItems[$oldItem->getSourceItemId()] = $oldItem;
            }
        }

        // Update/create items
        foreach ($sourceReport->getItems() as $reportItem) {
            $this->copyItem($reportItem, $report, $peerGroupId);
            unset($oldItems[$reportItem->getId()]);
        }

        // Now $oldItems should only contain items to delete
        foreach ($oldItems as $oldItem) {
            $this->getReportItemModel()->delete($oldItem);
        }

        $this->getReportModel()->getEntityManager()->flush();

        return $report->getId();
    }

    protected function getOrCreateReport(User $user)
    {
        if ($this->userHasReport($user)) {
            $report = $this->getReportToUpdate($user->getId());
        } else {
            $report = new Report;
            $report->setUser($user);
            $report->setStudy($this->currentStudy());
        }

        return $report;
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

    protected function getSamplePeerGroup($user)
    {
        $sampleGroup = $this->getPeerGroupModel()->find($this->peerGroupIdToCopy);

        $group = null;
        if ($sampleGroup) {
            $group = $this->getPeerGroupModel()->findOneByUserAndName($user, $sampleGroup->getName());
        }

        return $group;
    }

    /**
     * @param $peerGroupIdToCopy
     * @param User $user
     * @return null
     * @internal param College $college
     */
    protected function copyPeerGroup($peerGroupIdToCopy, User $user, $addThisCollege = false)
    {
        $newPeerGroupId = null;
        if (!empty($peerGroupIdToCopy)) {
            $sampleGroup = $this->getPeerGroupModel()->find($peerGroupIdToCopy);
            $peers = $sampleGroup->getPeers();

            // Remove the owning college from the peer group
            if (($key = array_search($user->getCollege()->getId(), $peers)) !== false) {
                unset($peers[$key]);
            }

            // Add the source college to the destination peer group?
            if ($addThisCollege) {
                $sourceCollege = $sampleGroup->getCollege();
                if (empty($sourceCollege)) {
                    $sourceCollege = $sampleGroup->getUser()->getCollege();
                }

                if (!empty($sourceCollege)) {
                    $sourceCollegeId = $sourceCollege->getId();

                    if (!in_array($sourceCollege, $peers)) {
                        $peers[] = $sourceCollegeId;
                    }
                }
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
     * Strip out anything that should not be copied.
     *
     * @param $sourceItem
     * @param $newReport
     * @param $peerGroupId
     */
    protected function copyItem(ReportItem $sourceItem, Report $newReport, $peerGroupId = null)
    {
        $config = $sourceItem->getConfig();
        if ($peerGroupId != 'same') {
            $config['peerGroup'] = $peerGroupId;
        }

        $sourceCollege = $sourceItem->getReport()->getUser()->getCollege();
        $targetCollege = $newReport->getUser()->getCollege();

        //pr($targetCollege->getId());
        //pr($config);


        $config = $this->handleItemColleges($config, $sourceCollege, $targetCollege);
        $config = $this->handleItemCollegeColor($config, $sourceCollege, $targetCollege);

        //prd($config);
        //prd($config);

        $item = $this->getOrCreateItem($sourceItem, $newReport->getId());
        $item->setReport($newReport);

        $item->setSequence($sourceItem->getSequence());
        $item->setType($sourceItem->getType());
        $item->setCache(null);
        $item->setDescription($sourceItem->getDescription());
        $item->setYear($sourceItem->getYear());
        $item->setConfig($config);
        $item->setSourceItemId($sourceItem->getId());
        $item->setHighlightedCollege($targetCollege);

        $this->getReportItemModel()->save($item);
    }

    /**
     * Prevent duplicates when copying
     *
     * @param $config
     * @param $sourceCollege
     * @param $targetCollege
     * @return mixed
     */
    protected function handleItemColleges($config, $sourceCollege, $targetCollege)
    {
        // Is the target college listed in the highlighted colleges?
        if (isset($config['colleges']) && in_array($targetCollege->getId(), $config['colleges'])) {
            // Remove it
            $newColleges = array_diff($config['colleges'], array($targetCollege->getId()));

            // Replace it with the source college
            $newColleges[] = $sourceCollege->getId();

            $config['colleges'] = $newColleges;
        }

        return $config;
    }

    protected function handleItemCollegeColor($config, College $sourceCollege, College $targetCollege)
    {
        // Only do this if My Data is not hidden
        if (empty($config['hideMine']) && !empty($config['colors'])) {
            $colors = json_decode($config['colors'], true);

            $sourceName = $sourceCollege->getNameAndState();
            $targetName = $targetCollege->getNameAndState();

            if (!empty($colors[$sourceName])) {
                $myDataColor = $colors[$sourceName];
                unset($colors[$sourceName]);

                $colors[$targetName] = $myDataColor;

                $config['colors'] = json_encode($colors);
            }

        }

        return $config;
    }

    protected function getOrCreateItem(ReportItem $sourceItem, $reportId)
    {
        if ($item = $this->getReportItemModel()->findBySourceItem($sourceItem->getId(), $reportId)) {
            // Found it. Just update it
        } else {
            // None yet. Create one.
            $item = new ReportItem();
        }


        $item->setName($sourceItem->getName());

        return $item;
    }

    /**
     * @return \Mrss\Model\Setting
     */
    public function getSettingModel()
    {
        return $this->getServiceLocator()->get('model.setting');
    }
}
