<?php

namespace Sample\YahooFinanceBundle\Entity\Repository;

use Sample\UserBundle\Entity\User;

/**
 * StockSymbolRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StockSymbolRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * Returns unused StockSymbols by user
     * @param User $user
     * @return array
     */
    public function findUnusedStocks(User $user)
    {
		$usedSymbols = $user->getStockSymbols();
        $usedSymbolIds = array();
        foreach( $usedSymbols as $usedSymbol )
        {
            $usedSymbolIds[] = $usedSymbol->getId();
        }

        $queryBuilder = $this->createQueryBuilder('s')->select('s');
        if( !empty($usedSymbolIds) )
        {
            $queryBuilder = $queryBuilder->where('s NOT IN (:usedSymbols)')
                    ->setParameter('usedSymbols', $user->getStockSymbols());
        }

        $queryBuilder = $queryBuilder->orderBy('s.symbol', 'ASC');

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    /**
     * Returns all StockSymbols
     * 
     * @return array
     */
    public function queryAll()
    {
        $queryBuilder = $this->createQueryBuilder('s')->select('s')->orderBy('s.symbol', 'ASC');
        return $queryBuilder;
    }
}
