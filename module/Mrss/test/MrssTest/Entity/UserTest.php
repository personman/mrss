<?php
/**
 * Test the user entity
 */
namespace MrssTest\Entity;

use Mrss\Entity\User;
use PHPUnit_Framework_TestCase;

/**
 * Class UserTest
 *
 * @package MrssTest\Entity
 */
class UserTest extends PHPUnit_Framework_TestCase
{
    /**
     * Are the class variables initialized correctly?
     *
     * @return null
     */
    public function testUserInitialState()
    {
        $user = new User();

        $this->assertNull(
            $user->getUsername(),
            '"username" should initially be null'
        );

        $this->assertNull(
            $user->getId(),
            '"id" should initially be null'
        );

        $this->assertNull(
            $user->getEmail(),
            '"email" should initially be null'
        );

        $this->assertNull(
            $user->getPassword(),
            '"password" should initially be null'
        );

        $this->assertNull(
            $user->getState(),
            '"state" should initially be null'
        );

        $this->assertNull(
            $user->getCollege(),
            '"college" should initially be null'
        );

        $this->assertNull(
            $user->getPrefix(),
            '"prefix" should initially be null'
        );

        $this->assertNull(
            $user->getFirstName(),
            '"firstName" should initially be null'
        );

        $this->assertNull(
            $user->getLastName(),
            '"lastName" should initially be null'
        );

        $this->assertNull(
            $user->getTitle(),
            '"title" should initially be null'
        );

        $this->assertNull(
            $user->getPhone(),
            '"phone" should initially be null'
        );

        $this->assertNull(
            $user->getExtension(),
            '"extension" should initially be null'
        );

        $this->assertNull(
            $user->getLastAccess(),
            '"lastAccess should initially be null'
        );

        $this->assertEquals(
            'user',
            $user->getRole()
        );

    }

    public function testInterfaces()
    {
        $user = new User();

        $this->assertInstanceOf(
            'BjyAuthorize\Provider\Role\ProviderInterface',
            $user
        );

        $this->assertInstanceOf(
            'ZfcUser\Entity\UserInterface',
            $user
        );
    }

    public function testSetters()
    {
        $user = new User();

        $userData = array(
            'id' => 5,
            'username' => 'johndoe',
            'displayName' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => 'ksdfkjsfien;sdis',
            'state' => true,
            'prefix' => 'Mr.',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'title' => 'Tester',
            'phone' => '111-111-1111',
            'extension' => '1234',
            'role' => 'user',
            'lastAccess' => new \DateTime('now')
        );

        $user->setId($userData['id']);
        $this->assertEquals($userData['id'], $user->getId());

        $user->setUsername($userData['username']);
        $this->assertEquals($userData['username'], $user->getUsername());

        $user->setEmail($userData['email']);
        $this->assertEquals($userData['email'], $user->getEmail());

        $user->setDisplayName('test');
        $user->setPrefix('Mr.');
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $this->assertEquals('Mr. John Doe', $user->getDisplayName());

        $user->setPassword($userData['password']);
        $this->assertEquals($userData['password'], $user->getPassword());

        $user->setState($userData['state']);
        $this->assertEquals($userData['state'], $user->getState());
        
        $user->setPrefix($userData['prefix']);
        $this->assertEquals($userData['prefix'], $user->getPrefix());

        $user->setFirstname($userData['firstName']);
        $this->assertEquals($userData['firstName'], $user->getFirstname());

        $user->setLastname($userData['lastName']);
        $this->assertEquals($userData['lastName'], $user->getLastname());

        $user->setTitle($userData['title']);
        $this->assertEquals($userData['title'], $user->getTitle());

        $user->setPhone($userData['phone']);
        $this->assertEquals($userData['phone'], $user->getPhone());

        $user->setExtension($userData['extension']);
        $this->assertEquals($userData['extension'], $user->getExtension());

        $user->setLastAccess($userData['lastAccess']);
        $this->assertEquals($userData['lastAccess'], $user->getLastAccess());

        $user->setRole($userData['role']);
        $this->assertEquals($userData['role'], $user->getRole());
    }

    public function testGetRoles()
    {
        $user = new User;

        $user->setRole('admin');

        $this->assertEquals(array('admin'), $user->getRoles());
    }

    public function testSetCollege()
    {
        $user = new User;

        $collegeMock = $this->getMock(
            'Mrss\Entity\College'
        );

        $user->setCollege($collegeMock);

        $this->assertSame($collegeMock, $user->getCollege());
    }

    public function testGetFullName()
    {
        $user = new User;

        $user->setPrefix('Dr.');
        $user->setFirstName('Tobias');
        $user->setLastName('Funke');

        $this->assertEquals('Dr. Tobias Funke', $user->getFullName());
    }

    public function testCheckCollege()
    {
        $user = new User;

        $studyMock = $this->getMock(
            '\Mrss\Entity\Study',
            array('getId')
        );
        $studyMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(5));

        $user->addStudy($studyMock);

        $result = $user->hasStudy($studyMock);

        $this->assertTrue($result);
    }

    public function testCheckCollegeNope()
    {
        $user = new User;

        $studyMock = $this->getMock(
            '\Mrss\Entity\Study',
            array('getId')
        );
        $studyMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(5));

        //$user->addStudy($studyMock);

        $result = $user->hasStudy($studyMock);

        $this->assertFalse($result);
    }

    public function testCheckCollegeRemove()
    {
        $user = new User;

        $studyMock = $this->getMock(
            '\Mrss\Entity\Study',
            array('getId')
        );
        $studyMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(5));

        $user->addStudy($studyMock);

        $result = $user->hasStudy($studyMock);

        $this->assertTrue($result);

        // Now remove
        $user->removeStudy($studyMock);

        $result = $user->hasStudy($studyMock);

        $this->assertFalse($result);
    }
}
