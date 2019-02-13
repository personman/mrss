<?php

namespace Mrss\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Mrss\Entity\User;
use Mrss\Controller\Plugin\SystemActiveCollege;
use Mrss\Controller\Plugin\CurrentStudy as CurrentStudyPlugin;
use Zend\Form\Form;
use Zend\Session\Container;

/**
 * If the user is a system admin, let them switch colleges
 */
class SystemAdmin extends AbstractHelper
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var SystemActiveCollege
     */
    protected $activeCollegePlugin;

    /**
     * @var CurrentStudy
     */
    protected $currentStudyPlugin;

    protected $activeCollege;

    protected $activeSysContainer;

    protected $systemModel;

    protected $studyConfig;

    public function __invoke($allowed = false)
    {

        $allowed = $this->getAllowed();

        return $this->showCollegeSwitcher($allowed);
    }

    protected function getAllowed()
    {
        $user = $this->getUser();
        $systemId = $this->getActiveSystemId($user);

        $allowed = false;
        if ($user) {
            if ($user->getRole() == 'system_admin') {
                $allowed = $user->administersSystem($systemId);
            } elseif ($user->getRole() == 'system_viewer') {
                $allowed = $user->viewsSystem($systemId);
            }
        }

        return $allowed;
    }

    public function showCollegeSwitcher($allowed)
    {
        $html = '';

        $user = $this->getUser();

        if (!$user) {
            return false;
        }

        if ($user->getRole() == 'system_admin' && !$user->administersSystem($this->getActiveSystemId())) {
            return false;

        }

        if ($user->getRole() == 'system_viewer' && !$user->viewsSystem($this->getActiveSystemId())) {
            return false;
        }

        if (!empty($user) && $allowed) {
            $activeCollege = $this->getActiveCollege();

            $collegeSelected = true;
            if (empty($activeCollege)) {
                $activeCollege = $user->getCollege();
                $collegeSelected = false;
            }


            $collegeName = $activeCollege->getName();
            if (!$activeCollege->getSystem()) {
                return "$collegeName does not belong to a system, but has a system admin user.";
            }

            $systemName = $activeCollege->getSystem()->getName();

            $form = $this->getSwitchForm();
            $overviewUrl = $this->getOverviewUrl();

            $returnOr = null;
            if ($collegeSelected) {
                $returnOr = "<a href='$overviewUrl'>return to the $systemName system</a> or ";
            }

            $label = $this->getInstitutionLabel();

            $html = "<div class='well system-impersonation'>
                You are working as <strong>$collegeName</strong>. You
                may $returnOr switch to another $label:
                $form
            </div>";

        }

        return $html;
    }

    protected function getInstitutionLabel($addIndefiniteArticle = false)
    {
        $label = $this->getStudyConfig()->institution_label;
        $label = strtolower($label);

        if ($addIndefiniteArticle) {
            $vowels = array('a', 'e', 'i', 'o', 'u');
            $article = 'a';
            if (in_array($label[0], $vowels)) {
                $article = 'an';
            }

            $label = "$article $label";
        }

        return $label;
    }

    public function getOverviewUrl()
    {
        $url = $this->getView()->url(
            'users/switch',
            array('college_id' => 'overview')
        );

        return $url;
    }

    protected function getSwitchForm()
    {
        $form = new Form();
        $colleges = $this->getColleges();

        $form->setAttribute('method', 'get');
        $form->setAttribute('action', '/users/switch');
        $form->setAttribute('class', 'form-horizontal');
        $form->setAttribute('id', 'system-admin-switch');


        $value = null;
        if ($college = $this->getActiveCollege()) {
            $value = $college->getId();
        }

        $institutionLabel = $this->getInstitutionLabel(true);


        $form->add(
            array(
                'name' => 'college_id',
                'type' => 'Select',
                'options' => array(
                    'empty_option' => "== Choose $institutionLabel . ==",
                ),
                'attributes' => array(
                    'value' => $value,
                    'options' => $colleges
                )
            )
        );

        $form->add(
            array(
                'name' => 'submit',
                'type' => 'Submit',
                'attributes' => array(
                    'value' => 'Switch',
                    'class' => 'btn btn-default'
                )
            )
        );

        // Return a string of html
        $html = '';
        $form->prepare();
        $html .= $this->getView()->form()->openTag($form);
        $html .= $this->getView()->formRow($form->get('college_id'));
        $html .= $this->getView()->formSubmit($form->get('submit'));
        $html .= $this->getView()->form()->closeTag();

        return $html;
    }

    /**
     * This should only return colleges that have a subscription for the
     * current year and study.
     * @return array
     * @throws \Exception
     */
    protected function getColleges()
    {
        $requireSubscription = false;

        //$system = $this->getUser()->getCollege()->getSystem();
        $systemId = $this->getActiveSystemId();
        $system = $this->getSystemModel()->find($systemId);

        if (empty($system)) {
            throw new \Exception('System not found');
        }

        /** @var \Mrss\Entity\Study $study */
        $study = $this->getCurrentStudyPlugin()->getCurrentStudy();
        $colleges = $system->getColleges();
        $collegesKeyed = array();
        foreach ($colleges as $college) {
            // Make sure there's a subscription
            if ($requireSubscription) {
                $subscription = $college->getSubscriptionByStudyAndYear(
                    $study->getId(),
                    $study->getCurrentYear()
                );

                if (empty($subscription)) {
                    continue;
                }
            }

            $collegesKeyed[$college->getId()] = $college->getName();
        }

        return $collegesKeyed;
    }

    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setActiveCollegePlugin(SystemActiveCollege $plugin)
    {
        $this->activeCollegePlugin = $plugin;

        return $this;
    }

    public function getActiveCollegePlugin()
    {
        return $this->activeCollegePlugin;
    }

    public function getActiveCollege()
    {
        if (empty($this->activeCollege)) {
            $this->activeCollege = $this->getActiveCollegePlugin()
                ->getActiveCollege();
        }

        return $this->activeCollege;
    }

    public function setCurrentStudyPlugin(CurrentStudyPlugin $currentStudyPlugin)
    {
        $this->currentStudyPlugin = $currentStudyPlugin;

        return $this;
    }

    /**
     * @return CurrentStudy
     */
    public function getCurrentStudyPlugin()
    {
        return $this->currentStudyPlugin;
    }

    protected function getActiveSystemId($user = false)
    {
        // This session container should match what's in BaseController
        $systemId = $this->getActiveSystemContainer()->system_id;

        // If there's nothing there yet, just grab the first system from their list
        if (empty($systemId) && !empty($user)) {
            $systemIds = $user->getSystemsAdministered(true);
            if ($systemIds) {
                $systemId = $systemIds[0];

                $this->getActiveSystemContainer()->system_id = $systemId;
            }
        }

        return $systemId;
    }

    protected function getActiveSystemContainer()
    {
        if (empty($this->activeSysContainer)) {
            $container = new Container('active_system');
            $this->activeSysContainer = $container;
        }

        return $this->activeSysContainer;
    }

    /**
     * @return \Mrss\Model\System
     */
    protected function getSystemModel()
    {
        return $this->systemModel;
    }

    /**
     * @param mixed $systemModel
     */
    public function setSystemModel($systemModel)
    {
        $this->systemModel = $systemModel;
    }

    /**
     * @return mixed
     */
    public function getStudyConfig()
    {
        return $this->studyConfig;
    }

    /**
     * @param mixed $studyConfig
     * @return $this
     */
    public function setStudyConfig($studyConfig)
    {
        $this->studyConfig = $studyConfig;

        return $this;
    }
}
