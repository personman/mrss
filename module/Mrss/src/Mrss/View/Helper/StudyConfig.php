<?php

namespace Mrss\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Return the current study
 */
class StudyConfig extends AbstractHelper
{
    protected $config;

    public function __invoke()
    {
        $return = $this->getConfig();

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
