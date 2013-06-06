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

    public function setPlugin(CurrentStudyPlugin $currentStudyPlugin)
    {
        $this->currentStudyPlugin = $currentStudyPlugin;
    }

    public function __invoke()
    {
        return $this->currentStudyPlugin->getCurrentStudy();
    }
}
