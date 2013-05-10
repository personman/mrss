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
            $user->getDisplayName(),
            '"display name" should initially be null'
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
            'extension' => '1234'
        );

        $user->setId($userData['id']);
        $this->assertEquals($userData['id'], $user->getId());

        $user->setUsername($userData['username']);
        $this->assertEquals($userData['username'], $user->getUsername());

        $user->setEmail($userData['email']);
        $this->assertEquals($userData['email'], $user->getEmail());

        $user->setDisplayName($userData['displayName']);
        $this->assertEquals($userData['displayName'], $user->getDisplayName());

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
}
