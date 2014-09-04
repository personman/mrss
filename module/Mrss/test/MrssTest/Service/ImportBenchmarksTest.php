<?php

namespace MrssTest\Service;

use PHPUnit_Framework_TestCase;
use Mrss\Service\ImportBenchmarks;
use Mrss\Entity\Benchmark;

class ImportBenchmarksTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ImportBenchmarks
     */
    protected $service;

    public function setUp()
    {
        $this->service = new ImportBenchmarks();
    }

    public function tearDown()
    {
        unset($this->service);
    }

    public function testGetMessages()
    {
        $benchmark = new Benchmark();
        $benchmark->setDbColumn('does_not_exist');
        $benchmark->setInputType('percent');

        $this->service->checkObservation($benchmark);
        $toAdd = $this->service->getMessages();

        $this->assertContains('@ORM', $toAdd);
        $this->assertContains('does_not_exist', $toAdd);
    }

    public function testGetTypeByInputType()
    {
        $this->assertEquals('string', $this->service->getTypeByInputType('radio'));
        $this->assertEquals('integer', $this->service->getTypeByInputType('number'));
        $this->assertEquals('float', $this->service->getTypeByInputType('percent'));
    }
}
