<?php
/**
 * Test the importer service
 */
namespace MrssTest\Service;

use Mrss\Service\ImportNccbp;
use PHPUnit_Framework_TestCase;
use Zend\Debug\Debug;

/**
 * Class ImportNccbp
 *
 * @package MrssTest\Service
 */
class ImportNccbpTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    /**
     * @var \Mrss\Service\ImportNccbp
     */
    protected $import;

    public function setUp()
    {
        $this->db = $this->getMock('Zend\Db\Adapter', array('query'));
        $this->em = $this->getEmMock();

        $this->import = new ImportNccbp($this->db, $this->em);
    }

    /**
     * Are the class variables initialized correctly?
     *
     * @return null
     */
    public function testInitialState()
    {
        $this->assertInstanceOf('Mrss\Service\ImportNccbp', $this->import);
    }

    /**
     * @todo: May be better of just mocking the model, instead of the ORM stuff.
     * This test is currently covering Mrss\Model\College. That shouldn't be.
     */
    public function testImportColleges()
    {
        $statementMock = $this->getMock('Zend\Db\Statement', array('execute'));
        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'field_ipeds_id_value' => '123',
                            'field_institution_name_value' => 'blah',
                            'field_ipeds_id_value' => '555555',
                            'field_address_value' => '123 Main',
                            'field_city_value' => 'Overland Park',
                            'field_state_value' => 'KS',
                            'field_zip_code_value' => '66101'
                        ),
                        array(
                            'field_ipeds_id_value' => '1234',
                            'field_institution_name_value' => 'blah',
                            'field_ipeds_id_value' => '444444',
                            'field_address_value' => '123 Main',
                            'field_city_value' => 'Overland Park',
                            'field_state_value' => 'KS',
                            'field_zip_code_value' => '66101'
                        )

                    )
                )
            );

        // Zend Db mock
        $this->db
            ->expects($this->once())
            ->method('query')
            ->will($this->returnValue($statementMock));


        // CollegeModel mock
        $collegeModelMock = $this->getMock(
            '\Mrss\Model\College',
            array('findOneByIpeds', 'save')
        );

        $collegeModelMock->expects($this->any())
            ->method('findOneByIpeds')
            ->will($this->onConsecutiveCalls(null, 'something'));

        $this->import->setCollegeModel($collegeModelMock);

        // Trigger the import itself
        $this->import->importColleges();
    }

    public function testGetStats()
    {
        $stats = $this->import->getStats();

        $this->assertEquals(0, $stats['imported']);
        $this->assertEquals(0, $stats['skipped']);
    }

    /**
     * @param $nccbpField
     * @param $converted
     * @dataProvider getFieldConversions
     */
    public function testConvertFieldName($nccbpField, $converted)
    {
        $this->assertEquals(
            $converted,
            $this->import->convertFieldName($nccbpField)
        );
    }

    /**
     * @param $invalidField
     * @expectedException \Exception
     * @dataProvider getInvalidFields
     */
    public function testConvertInvalidFieldName($invalidField)
    {
        $this->import->convertFieldName($invalidField);
    }

    /**
     * @param $nccbpField
     * @param $converted
     * @dataProvider getFieldConversionsNoValue
     */
    public function testConvertFieldNameNoIncludeValue($nccbpField, $converted)
    {
        $result = $this->import->convertFieldName($nccbpField, false);

        $this->assertEquals($converted, $result);
    }

    /**
     * @param $original
     * @param $padded
     * @dataProvider getIpeds
     */
    public function testPadIpeds($original, $padded)
    {
        $this->assertEquals($padded, $this->import->padIpeds($original));
    }

    /**
     * @dataProvider getTitleIpeds
     * @param $title
     * @param $ipeds
     */
    public function testExtractIpedsFromTitle($title, $ipeds)
    {
        $extracted = $this->import->extractIpedsFromTitle($title);

        $this->assertEquals($ipeds, $extracted);
    }

    /**
     * @param $type
     * @param $expected
     * @dataProvider getInputTypes
     */
    public function testConvertInputType($type, $expected)
    {
        $converted = $this->import->convertInputType($type);

        $this->assertEquals($expected, $converted);
    }

    public function testModelSetters()
    {
        $this->import->setObservationModel('placeholder');
        $this->assertEquals('placeholder', $this->import->getObservationModel());

        $this->import->setBenchmarkModel('placeholder');
        $this->assertEquals('placeholder', $this->import->getBenchmarkModel());
    }

    /**
     * Titles and expecged ipeds for extractIpedsFromTitle()
     *
     * @return array
     */
    public function getTitleIpeds()
    {
        return array(
            array(
                'form18_stud_serv_staff_Hutchinson Community College_155195',
                '155195'
            ),
            array(
                'form18_stud_serv_staff_Ivy Tech Community College of
                Indiana-Southwest_151050',
                '151050'
            ),
            array(
                'form18_stud_serv_staff_Ivy Tech Community College of Indiana-Northeast_151032',
                '151032'
            ),

        );
    }

    public function getIpeds()
    {
        return array(
            array('123456','123456'),
            array('812', '000812'),
            array('3200', '003200')
        );
    }

    public function getFieldConversions()
    {
        return array(
            array('field_18_tot_fte_fin_aid_staff_value', 'tot_fte_fin_aid_staff'),
            array('field_18_tot_fte_recr_staff_value', 'tot_fte_recr_staff'),
            array('field_18b_tot_fte_recr_staff_value', 'tot_fte_recr_staff')
        );
    }

    public function getInvalidFields()
    {
        return array(
            array('feld_18_blah_blah_value'),
            array('field_19_blah_blah'),
            array('field_19_value'),
            array('this_wont_work')
        );
    }

    public function getFieldConversionsNoValue()
    {
        return array(
            array('field_18_tot_fte_fin_aid_staff', 'tot_fte_fin_aid_staff'),
            array('field_18_tot_fte_recr_staff', 'tot_fte_recr_staff'),
            array('field_18a_tot_fte_recr_staff', 'tot_fte_recr_staff')
        );
    }

    public function getInputTypes()
    {
        return array(
            array('text_textfield', 'text'),
            array('number', 'number'),
            array('userreference_select', 'user'),
            array('something_made_up', 'something_made_up')
        );
    }


    protected function getEmMock()
    {
        $repositoryMock = $this->getMock(
            'Doctrine\Orm\Repository',
            array('findOneBy')
        );

        $emMock  = $this->getMock(
            '\Doctrine\ORM\EntityManager',
            array('getRepository', 'getClassMetadata', 'persist', 'flush'),
            array(),
            '',
            false
        );
        $emMock->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repositoryMock));
        $emMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue((object)array('name' => 'aClass')));
        $emMock->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(null));
        $emMock->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(null));

        return $emMock;
    }
}
