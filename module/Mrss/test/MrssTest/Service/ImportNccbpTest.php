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
                            'field_address_value' => '123 Main',
                            'field_city_value' => 'Overland Park',
                            'field_state_value' => 'KS',
                            'field_zip_code_value' => '66101'

                        )
                    )
                )
            );


        $this->db
            ->expects($this->once())
            ->method('query')
            ->will($this->returnValue($statementMock));



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
     * @param $original
     * @param $padded
     * @dataProvider getIpeds
     */
    public function testPadIpeds($original, $padded)
    {
        $this->assertEquals($padded, $this->import->padIpeds($original));
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
            array('field_18_tot_fte_recr_staff_value', 'tot_fte_recr_staff')
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
