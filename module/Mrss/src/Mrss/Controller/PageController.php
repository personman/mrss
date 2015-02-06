<?php


namespace Mrss\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Mrss\Entity\Page as PageEntity;
use Mrss\Form\Page as PageForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class PageController extends AbstractActionController
{
    public function indexAction()
    {
        $pageModel = $this->getServiceLocator()
            ->get('model.page');
        $pages = $pageModel->findAll();

        return array(
            'pages' => $pages
        );
    }

    public function editAction()
    {
        $id = $this->params('id');
        if (empty($id) && $this->getRequest()->isPost()) {
            $id = $this->params()->fromPost('id');
        }

        $pageModel = $this->getServiceLocator()
            ->get('model.page');
        $page = $pageModel->find($id);

        if (empty($page)) {
            $page = new PageEntity;
        }

        $em = $this->getServiceLocator()->get('em');

        // Build form
        $form = new PageForm($em);

        $form->setHydrator(new DoctrineHydrator($em, 'Mrss\Entity\Page'));
        $form->bind($page);
        $form->setInputFilter($page->getInputFilter());

        // Handle form submission
        if ($this->getRequest()->isPost()) {

            // Handle deletes
            $buttons = $this->params()->fromPost('buttons');
            if (!empty($buttons['delete'])) {
                // Delete it
                $pageModel->delete($page);

                // Message
                $this->flashMessenger()->addSuccessMessage('Page deleted.');

                // Redirect
                return $this->redirect()->toRoute('pages');
            }

            // Hand the POST data to the form for validation
            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $pageModel->save($page);
                $this->getServiceLocator()
                    ->get('em')
                    ->flush();

                // Update the routeCache
                // @todo: Only do this when the route or status changes
                $routeCacheService = $this->getServiceLocator()
                    ->get('service.routeCache');
                $routeCacheService->rebuild();

                $url = '/' . $page->getRoute();
                $this->flashMessenger()->addSuccessMessage(
                    "Page saved. <a href='$url'>View it</a>."
                );
                return $this->redirect()->toRoute('pages');
            }

        }

        return array(
            'form' => $form
        );
    }

    /**
     * Look a page up by its route and display it
     */
    public function viewAction()
    {
        $pageRoute = $this->params('pageRoute');
        if (empty($pageRoute)) {
            $pageRoute = '';
        }

        // Customize for NCCBP. report-only viewers can't access member home
        if ($pageRoute == 'members' && $this->currentStudy()->getId() == 1) {
            $auth = $this->getServiceLocator()->get('zfcuser_auth_service');

            if ($auth->hasIdentity()) {
                $user = $auth->getIdentity();
                if ($user->getRole() == 'viewer') {
                    // Redirect them to the executive report
                    return $this->redirect()->toUrl('/reports/executive');
                }

            }
        }

        $pageModel = $this->getServiceLocator()
            ->get('model.page');
        $page = $pageModel->findOneByRouteAndStudy(
            $pageRoute,
            $this->currentStudy()->getId()
        );

        if (empty($page)) {
            throw new \Exception('Page not found');
        }

        if (!$page->getShowWrapper()) {
            $this->layout()->noWrapper = true;
        }

        // Load from html file for dev
        $pageFromFile = null;
        if ($fileName = $this->params()->fromQuery('page')) {
            $directory = '/data/imports/pages/';
            $path = getcwd() . $directory . $fileName . '.html';

            if (file_exists($path)) {
                $pageFromFile = file_get_contents($path);
            } else {
                echo 'no file found here: ';
                prd($path);
            }
        }

        return array(
            'page' => $page,
            'pageFromFile' => $pageFromFile
        );
    }
}
