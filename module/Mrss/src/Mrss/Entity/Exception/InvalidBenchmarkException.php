<?php

namespace Mrss\Entity\Exception;

use Zend\ModuleManager\Listener\Exception\ExceptionInterface;

class InvalidBenchmarkException extends \InvalidArgumentException implements ExceptionInterface
{
}
