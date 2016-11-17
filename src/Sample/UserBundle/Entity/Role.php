<?php

namespace Sample\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * @author Yuriy Arutyunov <yabegemot@gmail.com>
 * @ORM\Entity(repositoryClass="Sample\UserBundle\Entity\Repository\RoleRepository")
 * @ORM\Table(name="role")
 */
class Role implements RoleInterface {

    const CLASS_NAME = __CLASS__;
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_USER = 'ROLE_USER';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=30)
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="role", type="string", length=30, unique=true)
     * @var string
     */
    private $role;

    /**
     * @ORM\Column(name="role_hierarchy", type="array", nullable=true)
     * @var array
     */
    private $roleHierarchy = array();

    /**
     * @ORM\Column(type="integer")
     * @var integer
     */
    private $priority;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="roles",cascade={"persist"})
     * @var ArrayCollection
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * To String
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->name;
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
     * Set name
     *
     * @param string $name
     * @return Role
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return Role
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @see RoleInterface
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set role hierarchy
     *
     * @param array $roleHierarchy
     * @return Role
     */
    public function setRoleHierarchy($roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;

        return $this;
    }

    /**
     * Get role hierarchy
     *
     * @return array 
     */
    public function getRoleHierarchy()
    {
        return $this->roleHierarchy;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     * @return Role
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Add users
     *
     * @param User $user
     * @return Role
     */
    public function addUser(User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove users
     *
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return ArrayCollection 
     */
    public function getUsers()
    {
        return $this->users;
    }
}
