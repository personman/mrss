<?php

namespace Mrss\Entity;

use BjyAuthorize\Provider\Role\ProviderInterface;
use Doctrine\ORM\Mapping as ORM;
use ZfcUser\Entity\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;

/** @ORM\Entity
 * @ORM\Table(name="users")
 */
class User implements UserInterface, ProviderInterface
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
     * @ORM\JoinColumn(name="college_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $college;

    /**
     * @ORM\ManyToMany(targetEntity="Study")
     * @ORM\JoinTable(name="users_studies",
     *      joinColumns={@ORM\JoinColumn(
     *          name="user_id", referencedColumnName="id", onDelete="CASCADE"
     *      )},
     *      inverseJoinColumns={@ORM\JoinColumn(
     *          name="study_id", referencedColumnName="id", onDelete="CASCADE"
     *      )}
     *      )
     */
    protected $studies;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $role;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastAccess;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $adminBenchmarkSorting;

    /**
     * User setting for data definition help-blocks in data entry
     *
     * 'hide' means hide all, 'show' means show all, NULL means show on hover
     *
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $dataDefinitions;

    /**
     * Bidirectional - One-To-Many (INVERSE SIDE)
     *
     * @ORM\OneToMany(targetEntity="PeerGroup", mappedBy="user")
     */
    private $peerGroups;

    /**
     * @ORM\Column(type="string", length=60)
     */
    protected $systemsAdministered = '';

    /**
     * @ORM\Column(type="string", length=60)
     */
    protected $systemsViewer = '';

    /**
     * @Gedmo\Mapping\Annotation\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @var \DateTime $created
     */
    protected $created;

    public function __construct()
    {
        $this->studies = new ArrayCollection();
        $this->systemsAdministered = '';
    }

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
        if (empty($this->username) || $this->username == '.') {
            $firstName = str_replace(array(' ', '\''), '.', $this->getFirstName());
            $lastName = str_replace(array(' ', '\''), '.', $this->getLastName());
            $this->username = $firstName . '.' . $lastName;
        }

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
        if ($username == '.') {
            $username = null;
        }

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
        //return $this->displayName;
        return $this->getFullName();
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

    /**
     * @return \Mrss\Entity\College
     */
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

    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    public function getRole()
    {
        $role = $this->role;
        if (empty($role)) {
            $role = 'user';
        }

        return $role;
    }

    public function isAdmin()
    {
        return ($this->getRole() == 'admin');
    }

    /**
     * @return \Zend\Permissions\Acl\Role\RoleInterface[]
     */
    public function getRoles()
    {
        return array($this->getRole());
    }

    public function setLastAccess($lastAccess)
    {
        $this->lastAccess = $lastAccess;

        return $this;
    }

    public function getLastAccess()
    {
        return $this->lastAccess;
    }

    /**
     * Passed value should be 'data-entry' or 'report'
     *
     * @param $sorting
     * @return $this
     */
    public function setAdminBenchmarkSorting($sorting)
    {
        $this->adminBenchmarkSorting = $sorting;

        return $this;
    }

    public function getAdminBenchmarkSorting()
    {
        // Default to report
        if (empty($this->adminBenchmarkSorting)) {
            $this->setAdminBenchmarkSorting('report');
        }

        return $this->adminBenchmarkSorting;
    }

    public function setDataDefinitions($def)
    {
        $this->dataDefinitions = $def;

        return $this;
    }

    public function getDataDefinitions()
    {
        return $this->dataDefinitions;
    }

    public function getFullName()
    {
        return $this->getPrefix() . ' ' . $this->getFirstName() . ' ' .
        $this->getLastName();
    }

    public function getFullPhone()
    {
        $phone = $this->getPhone();

        if ($ext = $this->getExtension()) {
            $phone .= ' x' . $ext;
        }

        return $phone;
    }

    public function addStudy(Study $study)
    {
        if (!$this->hasStudy($study)) {
            $this->studies->add($study);
        }
    }

    public function removeStudy(Study $study)
    {
        if ($this->hasStudy($study)) {
            $this->studies->removeElement($study);
        }
    }

    public function addStudies($studies)
    {
        foreach ($studies as $study) {
            $this->addStudy($study);
        }
    }

    public function removeStudies($studies)
    {
        foreach ($studies as $study) {
            $this->removeStudy($study);
        }
    }

    public function setStudies($studies)
    {
        $this->studies = $studies;

        return $this;
    }

    /**
     * @return ArrayCollection|Study[]
     */
    public function getStudies()
    {
        return $this->studies;
    }

    public function hasStudy(Study $studyToCheck)
    {
        foreach ($this->getStudies() as $study) {
            if ($study->getId() == $studyToCheck->getId()) {
                return true;
            }
        }

        return false;
    }

    public function setPeerGroups($groups)
    {
        $this->peerGroups = $groups;

        return $this;
    }

    /**
     * @return PeerGroup[]
     */
    public function getPeerGroups()
    {
        return $this->peerGroups;
    }

    /**
     * @param array $systemIds
     * @return $this
     */
    public function setSystemsAdministered($systemIds)
    {
        if (is_array($systemIds)) {
            $this->systemsAdministered = implode('|', $systemIds);
        } else {
            $this->systemsAdministered = $systemIds;
        }

        return $this;
    }

    public function getSystemsAdministered($asArray = false)
    {
        $systemIds = $this->systemsAdministered;

        if ($asArray) {
            if ($systemIds) {
                $systemIds = explode('|', $this->systemsAdministered);
            } else {
                $systemIds = array();
            }
        }

        return $systemIds;
    }

    public function addSystemAdministered($systemId)
    {
        $existingSystems = $this->getSystemsAdministered(true);

        if (!in_array($systemId, $existingSystems)) {
            $existingSystems[] = $systemId;
        }

        $this->setSystemsAdministered($existingSystems);
    }

    public function removeSystemAdministered($systemId)
    {
        $existingSystems = $this->getSystemsAdministered(true);

        if (($key = array_search($systemId, $existingSystems)) !== false) {
            unset($existingSystems[$key]);
        }

        $this->setSystemsAdministered($existingSystems);
    }

    /**
     * Check to see if the user is a system admin for the given system
     *
     * @param $systemId
     * @return bool
     */
    public function administersSystem($systemId)
    {
        $systemIds = $this->getSystemsAdministered(true);

        return (in_array($systemId, $systemIds));
    }

    /**
     * @param array $systemIds
     * @return $this
     */
    public function setSystemsViewer($systemIds)
    {
        $this->systemsViewer = implode('|', $systemIds);

        return $this;
    }

    public function getSystemsViewer($asArray = false)
    {
        $systemIds = $this->systemsViewer;
        if ($asArray) {
            if ($systemIds) {
                $systemIds = explode('|', $this->systemsViewer);
            } else {
                $systemIds = array();
            }
        }

        return $systemIds;
    }

    public function addSystemViewer($systemId)
    {
        $existingSystems = $this->getSystemsViewer(true);

        if (!in_array($systemId, $existingSystems)) {
            $existingSystems[] = $systemId;
        }

        $this->setSystemsViewer($existingSystems);
    }

    public function removeSystemViewer($systemId)
    {
        $existingSystems = $this->getSystemsViewer(true);

        if (($key = array_search($systemId, $existingSystems)) !== false) {
            unset($existingSystems[$key]);
        }

        $this->setSystemsViewer($existingSystems);
    }


    /**
     * @param $created
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }
}
