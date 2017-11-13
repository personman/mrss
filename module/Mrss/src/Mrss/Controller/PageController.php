<?php


namespace Mrss\Controller;

use Mrss\Entity\Page as PageEntity;
use Mrss\Entity\Page;
use Mrss\Form\Page as PageForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class PageController extends BaseController
{
    public function indexAction()
    {
        $pageModel = $this->getPageModel();
        $pages = $pageModel->findAll();

        return array(
            'pages' => $pages
        );
    }

    public function editAction()
    {
        $pageId = $this->params('id');
        if (empty($pageId) && $this->getRequest()->isPost()) {
            $pageId = $this->params()->fromPost('id');
        }

        $pageModel = $this->getServiceLocator()
            ->get('model.page');
        $page = $pageModel->find($pageId);

        if (empty($page)) {
            $page = new PageEntity;
        }

        $entityManager = $this->getEntityManager();

        // Build form
        $form = new PageForm($entityManager);

        $form->setHydrator(new DoctrineHydrator($entityManager, 'Mrss\Entity\Page'));
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

    protected function getRedirect()
    {
        $pageRoute = $this->getPageRoute();
        // Customize for NCCBP. report-only viewers can't access member home @todo: generalize
        if ($pageRoute == 'members' && $this->currentStudy()->getId() == 1) {
            $auth = $this->zfcUserAuthentication();

            if ($auth->hasIdentity()) {
                $user = $auth->getIdentity();
                if ($user->getRole() == 'viewer') {
                    // Redirect them to the executive report
                    return $this->redirect()->toUrl('/reports/executive');
                }
            }
        }
    }

    protected function getPageRoute()
    {
        $pageRoute = $this->params('pageRoute');
        if (empty($pageRoute)) {
            $pageRoute = '';
        }

        return $pageRoute;
    }

    /**
     * Look a page up by its route and display it
     */
    public function viewAction()
    {
        if ($redirect = $this->getRedirect()) {
            return $redirect;
        }

        $page = $this->getPageModel()->findOneByRouteAndStudy(
            $this->getPageRoute(),
            $this->currentStudy()->getId()
        );

        if (empty($page)) {
            throw new \Exception('Page not found');
        }

        $this->adjustLayout($page);

        return array(
            'page' => $page,
            'pageFromFile' => $this->getPageFromFile(),
            'wrapperId' => $this->getWrapperId($page)
        );
    }

    protected function getWrapperId(Page $page)
    {
        $wrapperId = $page->getRoute();
        if (empty($wrapperId)) {
            $wrapperId = 'home';
        }

        return $wrapperId;
    }

    protected function adjustLayout(Page $page)
    {
        if (!$page->getShowWrapper()) {
            $this->layout()->setOption('noWrapper', true);
        }
        $this->layout()->setOption('wrapperId', $this->getWrapperId($page));
    }

    protected function getPageFromFile()
    {
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

        return $pageFromFile;
    }
}
