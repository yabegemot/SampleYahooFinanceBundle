<?php

namespace Sample\UserBundle\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Sample\YahooFinanceBundle\Entity\StockSymbol;
use Sample\YahooFinanceBundle\Entity\Repository\StockSymbolRepository;
use Sample\UserBundle\Entity\User;

class UserPortfolioType extends AbstractType {

    /**
     * 
     * @var ContainerInterface
     */
    private $container;

    /**
     *
     * @var SecurityContext 
     */
    private $securityContext;

    /**
     * 
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->securityContext = $this->container->get('security.context');
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array();
        if( isset($options['data']) && $options['data'] instanceof User )
        {
            $choices = $options['data']->getPortfolioSymbols();
        }
        $builder->add('stockSymbols', 'entity', array(
                    'label' => 'Stock Symbols',
                    'class' => StockSymbol::class,
                    'property' => 'symbol',
                    'choices' => $choices,
                    'required' => true,
                    'multiple' => true,
                    'expanded' => true,
                    'preferred_choices' => $options['data']->getStockSymbols()->toArray(),
                    //'query_builder' => function (StockSymbolRepository $stockSymbolRepository) {
                    //    return $stockSymbolRepository->queryAll();
                    //},
                    'attr' => array()
                ));
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Sample\UserBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'form';
    }
}
