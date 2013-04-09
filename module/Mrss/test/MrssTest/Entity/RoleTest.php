<?php
/**
 * Test the role entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\Role;
use PHPUnit_Framework_TestCase;

/**
 * Class RoleTest
 *
 * @package MrssTest\Model
 */
class RollTest extends PHPUnit_Framework_TestCase
{
    /**
     * Are the class variables initialized correctly?
     *
     * @return null
     */
    public function testInitialState()
    {
        $role = new Role();

        $this->assertNull(
            $role->getId(),
            '"id" should initially be null'
        );

        $this->assertNull(
            $role->getRoleId(),
            '"roleId" should initially be null'
        );

        $this->assertNull(
            $role->getParent(),
            '"parent" should initially be null'
        );
    }

    /**
     * Can we set role properties and do they stick?
     *
     * @dataProvider getRoleData
     * @param array $roleData
     */
    public function testSetters($roleData)
    {
        $role = new Role;

        $role->setId($roleData['id']);
        $role->setRoleId($roleData['roleId']);
        $role->setParent($roleData['parent']);

        $this->assertEquals($roleData['id'], $role->getId());
        $this->assertEquals($roleData['roleId'], $role->getRoleId());
        $this->assertEquals($roleData['parent'], $role->getParent());
    }

    /**
     * Provides some valid role data
     *
     * @return array
     */
    public function getRoleData()
    {
        return array(
            array(
                array(
                    'id' => 5,
                    'roleId' => 'admin',
                    'parent' => $this->getMock(
                        'Mrss\Entity\Role'
                    )
                )
            )
        );
    }
}
