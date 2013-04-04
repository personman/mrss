<?php

namespace MrssTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Class AbstractControllerTestCase
 *
 * Extending AbstractHttpControllerTestCase is the new way to test ZF2 controllers
 *
 * @package MrssTest\Controller
 */
class AbstractControllerTestCase extends AbstractHttpControllerTestCase
{
    protected function getConfigPath()
    {
        $appBase = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        $configPath = $appBase . '/config/application.config.php';

        return $configPath;
    }
}
