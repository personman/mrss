<?php

namespace MrssTest\Service;

use Mrss\Service\VariableSubstitution;
use PHPUnit_Framework_TestCase;

class VariableSubstitutionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var VariableSubstitution
     */
    protected $service;

    public function setUp()
    {
        $this->service = new VariableSubstitution();
    }

    /**
     * @param $year
     * @param $text
     * @param $expected
     * @dataProvider getSubs
     */
    public function testSubstitute($year, $text, $expected)
    {
        $this->service->setStudyYear($year);
        $subbed = $this->service->substitute($text);
        $this->assertEquals($expected, $subbed);
    }

    public function getSubs()
    {
        return array(
            array(2014, 'blah', 'blah'),
            array(2014, 'from [year_minus_2]', 'from 2012'),
            array(2014, '[year_minus_2]-[year_minus_1]', '2012-2013'),
            array(2015, '[year_minus_2]-[year_minus_1]', '2013-2014'),
        );
    }
}
