<?php

namespace Mrss\Entity;

use Doctrine\ORM\Mapping as ORM;
use ZfcUser\Entity\UserInterface;

/** @ORM\Entity
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $prefix;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */

    protected $lastName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $displayName;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $phone;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $extension;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @var int
     */
    protected $state;

    /**
     * @ORM\ManyToOne(targetEntity="College", inversedBy="users")
     */
    protected $college;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id
     * @return UserInterface
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username.
     *
     * @param string $username
     * @return UserInterface
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email
     * @return UserInterface
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get displayName.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set displayName.
     *
     * @param string $displayName
     * @return UserInterface
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password
     * @return UserInterface
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state.
     *
     * @param int $state
     * @return UserInterface
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    public function setCollege(\Mrss\Entity\College $college)
    {
        $this->college = $college;

        return $this;
    }

    public function getCollege()
    {
        return $this->college;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    public function getExtension()
    {
        return $this->extension;
    }
}
