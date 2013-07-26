<?php

namespace Mrss\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Mrss\Entity\User;
use Mrss\Controller\Plugin\SystemActiveCollege;
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

    protected $activeCollege;

    public function __invoke()
    {
        return $this->showCollegeSwitcher();
    }

    public function showCollegeSwitcher()
    {
        $html = '';

        if ($this->getUser()->getRole() == 'system_admin') {
            $activeCollege = $this->getActiveCollege();

            if (!empty($activeCollege)) {
                $collegeName = $activeCollege->getName();
                $form = $this->getSwitchForm();
                $overviewUrl = $this->getOverviewUrl();

                $html = "<div class='well'>
                    You are entering data for <strong>$collegeName</strong>. You
                    may <a href='$overviewUrl'>return to the system overview</a>
                    or switch to another institution:
                    $form
                </div>";

            }
        }

        return $html;
    }

    public function getOverviewUrl()
    {
        $url = $this->getView()->url(
            'data-entry/switch',
            array('college_id' => 'overview')
        );

        return $url;
    }

    public function getSwitchForm()
    {
        $form = new Form();
        $colleges = $this->getColleges();

        $form->setAttribute('method', 'get');
        $form->setAttribute('action', '/data-entry/switch');
        $form->setAttribute('class', 'form-horizontal');

        $form->add(
            array(
                'name' => 'college_id',
                'type' => 'Select',
                'attributes' => array(
                    'value' => $this->getActiveCollege()->getId(),
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
                    'class' => 'btn'
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

    public function getColleges()
    {
        $system = $this->getUser()->getCollege()->getSystem();

        if (empty($system)) {
            throw new \Exception('System not found');
        }

        $colleges = $system->getColleges();
        $collegesKeyed = array();
        foreach ($colleges as $college) {
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
}
