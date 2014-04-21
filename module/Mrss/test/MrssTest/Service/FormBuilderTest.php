<?php
/**
 * Test the formBuilder service
 */
namespace MrssTest\Service;

use Mrss\Service\FormBuilder;
use PHPUnit_Framework_TestCase;
use Zend\Debug\Debug;

/**
 * Class FormBuilderTest
 *
 * @package MrssTest\Service
 */
class FormBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testBuildForm()
    {
        $formBuilder = new FormBuilder;

        // Mock a form element array
        $formElementMock = array(
            'name' => 'some_db_col',
            'options' => array(
                'label' => 'Graduation Rate',
                'help-block' => 'blah blah'
            ),
            'attributes' => array(
                'class' => 'input-small'
            )
        );

        // Mock an individual benchmark
        $benchmarkMock = $this->getMock(
            'Mrss\Entity\Benchmark',
            array('getFormElement')
        );
        $benchmarkMock->expects($this->once())
            ->method('getFormElement')
            ->will($this->returnValue($formElementMock));

        $provider = $this->getMock(
            'Mrss\Entity\BenchmarkGroup',
            array('getElements', 'getUseSubObservation')
        );

        $provider->expects($this->once())
            ->method('getElements')
            ->will($this->returnValue(array($benchmarkMock)));

        $provider->expects($this->once())
            ->method('getUseSubObservation')
            ->will($this->returnValue(true));

        $form = $formBuilder->buildForm($provider, 2012);

        $this->assertInstanceOf('Zend\Form\Form', $form);
    }
}
