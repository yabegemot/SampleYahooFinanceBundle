<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sample\UserBundle\Entity\User;
use Sample\UserBundle\Entity\Role;
use Sample\YahooFinanceBundle\Entity\StockSymbol;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161116233825 extends AbstractMigration implements ContainerAwareInterface {

    private $container;

    /**
     *
     * @var \Doctrine\Common\Persistence\ObjectManager $entity_manager Entity Manager Service
     */
    private $entity_manager;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        $tablePrefix = $this->container->getParameter("samplebundle.db.table_prefix");
        $this->entity_manager = $this->container->get("doctrine.orm.entity_manager");
        $roleRepository = $this->entity_manager->getRepository('SampleUserBundle:Role');

        if( true === $schema->hasTable( $tablePrefix.'role' ) )
        {
            $securityRoleHierarchies = $this->container->getParameter('security.role_hierarchy.roles');

            foreach( $securityRoleHierarchies as $securityRole => $securityRoleHierarchy )
            {
                if( null !== ( $role = $roleRepository->findOneByRole($securityRole) ) )
                {
                    echo 'updating role: '.$role->getRole() . "\n";
                    $role->setName($this->getName($securityRole));
                    $role->setRoleHierarchy( $securityRoleHierarchy );
                    $role->setPriority( $this->getPriority($securityRole) );
                    $this->entity_manager->persist($role);
                    $this->entity_manager->flush();
                } else {
                    echo 'adding role: ' . $securityRole . "\n";
                    $role = new Role();
                    $role->setName($this->getName($securityRole));
                    $role->setRole($securityRole);
                    $role->setRoleHierarchy( $securityRoleHierarchy );
                    $role->setPriority( $this->getPriority($securityRole) );
                    $this->entity_manager->persist($role);
                    $this->entity_manager->flush();
                }
            }
        }
        else
        {
            echo 'Table not found...'.$tablePrefix.'role'."\n";
        }

        if( true === $schema->hasTable($tablePrefix.'user') )
        {
            $userRepository = $this->entity_manager->getRepository('SampleUserBundle:User');
            if( !$userRepository->findOneBy(array('username' => 'root')) )
            {
                echo 'adding superadmin'."\n";
                $superAdmin = new User();
                $superAdmin->setUsername('root');
                $encoder = $this->container->get('security.password_encoder');
                $superAdmin->setPassword($encoder->encodePassword($superAdmin, 'root'));
                $superAdmin->setIsActive(true);
                $superAdmin->setEmail('root@sample.com');
                $superAdmin->addRole($roleRepository->findOneBy(array('role' => Role::ROLE_SUPER_ADMIN)));
                $this->entity_manager->persist($superAdmin);
                $this->entity_manager->flush();
            }
        }
        else
        {
            echo 'Table not found...'.$tablePrefix.'user'."\n";
        }

        if( true === $schema->hasTable($tablePrefix.'stock_symbol') )
        {
            $stockSymbolRepository = $this->entity_manager->getRepository('SampleYahooFinanceBundle:StockSymbol');
            $stockSymbols = array('KO','INTC','WMT','AAPL','NSFT','NKE','JPM','GM','BAC','GOOG','C','PG','BA','TWX','YHOO');
            foreach( $stockSymbols as $symbol )
            {
                if( !$stockSymbolRepository->findOneBy(array('symbol' => $symbol)) )
                {
echo 'adding symbol: '.$symbol."\n";
                    $stockSymbol = new StockSymbol();
                    $stockSymbol->setSymbol($symbol);
                    $this->entity_manager->persist($stockSymbol);
                    $this->entity_manager->flush();
                }
            }
        }
        else
        {
            echo 'Table not found...'.$tablePrefix.'user'."\n";
        }
    }

    private function getPriority($securityRole) {

        $priority = 0;

        switch( $securityRole ) {
            case Role::ROLE_USER:
                $priority = 3;
                break;
            case Role::ROLE_ADMIN:
                $priority = 2;
                break;
            case Role::ROLE_SUPER_ADMIN:
                $priority = 1;
                break;
            default:
                $priority = 0;
                break;
        }

        return $priority;
    }

    private function getName($securityRole) {

        $name = '';

        switch( $securityRole ) {
            case Role::ROLE_USER:
                $name = 'User';
                break;
            case Role::ROLE_ADMIN:
                $name = 'Administrator';
                break;
            case Role::ROLE_SUPER_ADMIN:
                $name = 'Super Admin';
                break;
            default:
                $name = '';
                break;
        }

        return $name;
    }

    public function down(Schema $schema) {

    }
}
