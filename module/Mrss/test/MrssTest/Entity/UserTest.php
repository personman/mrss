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

        $this->assertNull($user->getUsername(), '"username" should initially
        be null');
        $this->assertNull($user->getId(), '"id" should initially be null');
        $this->assertNull($user->getEmail(), '"email" should initially be null');
        $this->assertNull($user->getDisplayName(), '"display name" should initially
        be null');
        $this->assertNull($user->getPassword(), '"password" should initially be
        null');
        $this->assertNull($user->getState(), '"state" should initially be
        null');

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
            'state' => true
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
    }
}
