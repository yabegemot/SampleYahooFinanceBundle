<?php

namespace Sample\YahooFinanceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Yuriy Arutyunov <yabegemot@gmail.com>
 * @ORM\Entity(repositoryClass="Sample\YahooFinanceBundle\Entity\Repository\StockSymbolRepository")
 * @ORM\Table(name="stock_symbol")
 */
class StockSymbol {

    const CLASS_NAME = __CLASS__;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=60, nullable=false)
     * @var string
     */
    private $symbol;

    /**
     * @ORM\ManyToMany(targetEntity="Sample\UserBundle\Entity\User", inversedBy="stockSymbols", cascade={"persist","remove"})
     * @var ArrayCollection
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity="Sample\UserBundle\Entity\User", inversedBy="portfolioSymbols", cascade={"persist","remove"})
     * @ORM\JoinTable(name="stock_symbol_user_portfolio")
     * @var ArrayCollection
     */
    private $userPortfolios;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->userPortfolios = new ArrayCollection();
    }

    /**
     * To String
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->symbol;
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
     * Set symbol
     *
     * @param string $symbol
     *
     * @return StockSymbol
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Add user
     *
     * @param \Sample\UserBundle\Entity\User $user
     *
     * @return StockSymbol
     */
    public function addUser(\Sample\UserBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Check if user exists
     *
     * @param \Sample\UserBundle\Entity\User $user
     *
     * @return boolean
     */
    public function hasUser(\Sample\UserBundle\Entity\User $user)
    {
        return $this->users->contains($user);
    }

    /**
     * Remove user
     *
     * @param \Sample\UserBundle\Entity\User $user
     */
    public function removeUser(\Sample\UserBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add userPortfolio
     *
     * @param \Sample\UserBundle\Entity\User $userPortfolio
     *
     * @return StockSymbol
     */
    public function addUserPortfolio(\Sample\UserBundle\Entity\User $userPortfolio)
    {
        $this->userPortfolios[] = $userPortfolio;

        return $this;
    }

    /**
     * Check if stock symbol exist in user portfolio list
     *
     * @param \Sample\UserBundle\Entity\User $userPortfolio
     *
     * @return StockSymbol
     */
    public function hasPortfolioUser(\Sample\UserBundle\Entity\User $userPortfolio)
    {
        return $this->userPortfolios->contains($userPortfolio);

        return $this;
    }

    /**
     * Remove userPortfolio
     *
     * @param \Sample\UserBundle\Entity\User $userPortfolio
     */
    public function removeUserPortfolio(\Sample\UserBundle\Entity\User $userPortfolio)
    {
        $this->userPortfolios->removeElement($userPortfolio);
    }

    /**
     * Get userPortfolios
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserPortfolios()
    {
        return $this->userPortfolios;
    }
}
