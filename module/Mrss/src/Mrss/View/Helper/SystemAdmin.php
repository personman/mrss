<?php

namespace Mrss\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Mrss\Entity\User;
use Mrss\Controller\Plugin\SystemActiveCollege;
use Mrss\Controller\Plugin\CurrentStudy as CurrentStudyPlugin;
use Zend\Form\Form;

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

    public function __invoke($allowed)
    {
        return $this->showCollegeSwitcher($allowed);
    }

    public function showCollegeSwitcher($allowed)
    {
        $html = '';

        $user = $this->getUser();
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

            $html = "<div class='well'>
                You are working as <strong>$collegeName</strong>. You
                may $returnOr switch to another institution:
                $form
            </div>";

        }

        return $html;
    }

    public function getOverviewUrl()
    {
        $url = $this->getView()->url(
            'users/switch',
            array('college_id' => 'overview')
        );

        return $url;
    }

    public function getSwitchForm()
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

        $form->add(
            array(
                'name' => 'college_id',
                'type' => 'Select',
                'options' => array(
                    'empty_option' => '== Choose an institution. ==',
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
    public function getColleges()
    {
        $requireSubscription = false;

        $system = $this->getUser()->getCollege()->getSystem();

        if (empty($system)) {
            throw new \Exception('System not found');
        }

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

    public function getCurrentStudyPlugin()
    {
        return $this->currentStudyPlugin;
    }
}
