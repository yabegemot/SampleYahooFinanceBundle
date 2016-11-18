<?php

namespace Sample\UserBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Yuriy Arutyunov <yabegemot@gmail.com>
 * @ORM\Entity(repositoryClass="Sample\UserBundle\Entity\Repository\UserRepository")
 * @ORM\Table(name="user")
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface, Serializable {

    const CLASS_NAME = __CLASS__;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @var string
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=128)
     * @var string
     */
    private $password;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users",cascade={"persist"})
     * @ORM\JoinTable(name="user_role")
     * @var ArrayCollection $roles
     */
    private $roles;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @var string
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     * @var string
     */
    private $last_name;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @var string
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @var string
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @var string
     */
    private $mobile;

    /**
     * @ORM\Column(name="isActive", type="boolean")
     * @var boolean
     */
    private $isActive;

    /**
     * @ORM\Column(name="createdAt", type="datetime", nullable=false)
     * @var DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updatedAt", type="datetime", nullable=false)
     * @var DateTime
     */
    private $updatedAt;

    /**
     * @ORM\ManyToMany(targetEntity="Sample\YahooFinanceBundle\Entity\StockSymbol", mappedBy="users", cascade={"persist","remove"})
     * @var ArrayCollection
     */
    private $stockSymbols;

    public function __construct()
    {
        $this->isActive = true;
        $this->roles = new ArrayCollection();
        $this->stockSymbols = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        if( !($this->createdAt instanceof \DataTable) )
        {
            $this->createdAt = new \DateTime();
        }
        if( !($this->updatedAt instanceof \DataTable) )
        {
            $this->updatedAt = new \DateTime();
        }
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    public function __toString()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
        ));
    }

    /**
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
                $this->id,
                $this->username,
                $this->password,
                ) = unserialize($serialized);
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     * 
     * @inheritDoc
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     * 
     * @inheritDoc
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        
    }

    /**
     * Add role
     *
     * @param Role $role
     * @return User
     */
    public function addRole(Role $role)
    {
        $this->roles[] = $role;

        return $this;
    }

    /**
     * Remove role
     *
     * @param Role $role
     * @return void
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Get user Roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * Returns Max Role
     * 
     * @return Role
     */
    public function getMaxRole()
    {
        /**
         * @var Role Description
         */
        $maxRole = $this->roles->first();

        if ($maxRole === FALSE)
        {
            return null;
        }

        $getMaxRole = function ($key, Role $role) use (&$maxRole)
        {
            if ($role->getPriority() > $maxRole->getPriority())
            {
                $maxRole = $role;
            }
        };

        $this->roles->forAll($getMaxRole);

        return $maxRole;
    }

    /**
     * Checks if user has role
     * 
     * @param string $roleRole
     * @return boolean
     */
    public function hasRole($roleRole)
    {
        foreach ($this->roles as $role)
        {
            if ($role->getRole() == $roleRole)
            {
                return true;
            }
            foreach ($role->getRoleHierarchy() as $hierarchyRole)
            {
                if ($hierarchyRole == $roleRole)
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Checks if role is user max role
     * 
     * @param string $roleRole
     * @return boolean
     */
    public function isMaxRole($roleRole)
    {
        return $this->getMaxRole()->getRole() == $roleRole;
    }

    /**
     * Set first_name
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get first_name
     *
     * @return string 
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get last_name
     *
     * @return string 
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return User
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param integer $phone
     * @return User
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     * @return User
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string 
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Set active
     *
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get if active
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Get if active
     *
     * @return boolean 
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return User
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add stockSymbol
     *
     * @param \Sample\YahooFinanceBundle\Entity\StockSymbol $stockSymbol
     *
     * @return User
     */
    public function addStockSymbol(\Sample\YahooFinanceBundle\Entity\StockSymbol $stockSymbol)
    {
        $this->stockSymbols[] = $stockSymbol;

        return $this;
    }

    /**
     * Check id stockSymbol exists
     *
     * @param \Sample\YahooFinanceBundle\Entity\StockSymbol $stockSymbol
     *
     * @return boolean
     */
    public function hasStockSymbol(\Sample\YahooFinanceBundle\Entity\StockSymbol $stockSymbol)
    {
        return $this->stockSymbols->contains($stockSymbol);
    }

    /**
     * Remove stockSymbol
     *
     * @param \Sample\YahooFinanceBundle\Entity\StockSymbol $stockSymbol
     */
    public function removeStockSymbol(\Sample\YahooFinanceBundle\Entity\StockSymbol $stockSymbol)
    {
        $this->stockSymbols->removeElement($stockSymbol);
    }

    /**
     * Get stockSymbols
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStockSymbols()
    {
        return $this->stockSymbols;
    }
}
