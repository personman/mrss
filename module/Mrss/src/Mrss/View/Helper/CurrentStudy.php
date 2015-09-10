<?php

namespace Mrss\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Mrss\Controller\Plugin\CurrentStudy as CurrentStudyPlugin;

/**
 * Return the current study
 */
class CurrentStudy extends AbstractHelper
{
    /**
     * @var CurrentStudyPlugin
     */
    protected $currentStudyPlugin;

    protected $config;

    public function setPlugin(CurrentStudyPlugin $currentStudyPlugin)
    {
        $this->currentStudyPlugin = $currentStudyPlugin;
    }

    public function __invoke($returnEntity = true)
    {
        if ($returnEntity) {
            $return = $this->currentStudyPlugin->getCurrentStudy();
        } else {
            $return = $this;
        }

        return $return;
    }

    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
