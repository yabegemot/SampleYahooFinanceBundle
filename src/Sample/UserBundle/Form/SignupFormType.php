<?php

namespace Sample\UserBundle\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

class SignupFormType extends AbstractType {

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

       $builder
            ->add('first_name', 'text', array( 'required' => false ))
            ->add('last_name', 'text', array( 'required' => false ))
            ->add('title', 'text', array( 'required' => false ))
            ->add('email', 'email', array('required' => true, 'attr' => array()))
            ->add('phone', 'text', array( 'required' => false ))
            ->add('mobile', 'text', array( 'required' => false ))
            ->add('username', 'text', array( 'required' => true ) )
            ->add('password', 'repeated', array(
                'type' => 'password',
                'required' => true,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'first_options' => array('label' => 'Password',
                    'attr' => array('placeholder' => 'Type Password', 'autocomplete' => "off")),
                'second_options' => array('label' => 'Confirm Password',
                    'attr' => array('placeholder' => 'Confirm Password', 'autocomplete' => "off"))
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
