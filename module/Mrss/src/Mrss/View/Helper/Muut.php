<?php

namespace Mrss\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Mrss\Entity\User;
use Zend\View\Helper\InlineScript;

/**
 * Display a chart
 */
class Muut extends AbstractHelper
{
    protected $name = 'govbenchmark';
    protected $key;
    protected $secret;

    protected $config = array();

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;

        if (!empty($config['name'])) {
            $this->name = $config['name'];
            $this->key = $config['key'];
            $this->secret = $config['secret'];
        }

    }

    public function __invoke(User $currentUser)
    {
        if ($this->getConfig()) {
            $this->addDependencies();

            if ($currentUser) {
                $this->showWithUser($currentUser);
            } else {
                $this->show();
            }
        } else {
            //echo 'Community disabled.';
        }
    }

    protected function addDependencies()
    {
        $this->getView()->headScript()->appendFile(
            "//cdn.muut.com/1/moot.min.js",
            'text/javascript'
        );

        $this->getView()->headLink()->appendStylesheet("//cdn.muut.com/1/moot.css");
    }

    protected function formatUser($currentUser)
    {
        $user = array(
            'user' => array(
                "id" => $currentUser->getId(),
                "displayname" => $currentUser->getDisplayName(),
                "email" => $currentUser->getEmail(),
                "avatar" => "//gravatar.com/avatar/" . md5(strtolower(trim($currentUser->getEmail()))),
                "is_admin" => ($currentUser->getRole() == 'admin'),
            ),
        );

        return $user;
    }

    protected function show()
    {
        $name = $this->name;
        echo '<a class="muut" href="https://muut.com/i/' . $name . '">' . $name . '</a>';
    }

    protected function showWithUser($user)
    {
        $key = $this->key;
        $user = $this->formatUser($user);

        $message = base64_encode(json_encode($user));
        $timestamp = time();
        $signature = sha1($this->secret . ' ' . $message . ' ' . $timestamp);

        echo '<a id="my-community" href="https://muut.com/i/' . $this->name . '">Community</a>';

        //$this->getView()->inlineScript()->captureStart();

        /** @var InlineScript $script */
        $script = $this->view->plugin('inlineScript');

        $script->captureStart();

        echo <<<JS
            var w = 'whereami';
            $('#my-community').muut({
                sso: true,
                api: {
                    // API key for "govbenchmark"- community
                    key: '$key',
                    message: '$message',
                    signature: '$signature',
                    timestamp: '$timestamp'
                }
            })
JS;
        //$this->getView()->inlineScript()->captureEnd();
        $script->captureEnd();

    }
}
