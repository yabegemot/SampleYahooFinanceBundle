<?php

namespace Sample\UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sample\UserBundle\Entity\User;
use Sample\UserBundle\Entity\Role;
use Sample\UserBundle\Form\SignupFormType;

/**
 * Description of FinanceController
 *
 * @author Yuriy Arutyunov <yabegemot@gmail.com>
 * @Route("/account")
 */
class UserController extends Controller
{
    /**
     *
     * @Route("/", name="account_home")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     * @Template("SampleUserBundle:Account:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * User sign up form action
     *
     * @Route("/sugnup", name="sugnup_form")
     * @Method("GET")
     * @Template("SampleUserBundle:Signup:form.html.twig")
     */
    public function signupFormAction(Request $request) {

        $user = new User();
        
        $form = $this->signupForm($user);

        return array(
            'form' => $form->createView()
        );
        
    }

    private function signupForm(User $user) {

        $form = $this->createForm(new SignupFormType($this->container), $user, array(
            'action' => $this->generateUrl('signup'),
            'method' => 'POST',
            'attr' => array('entity' => $user),
        ));

        $form->add('submit', 'submit', array('label' => 'Sign Up', 'attr' => array('class' => 'btn btn-primary btn-success')));

        return $form;
    }

    /**
     * User sign up action
     *
     * @Route("/sugnup", name="signup")
     * @Method("POST")
     * @Template("SampleUserBundle:Signup:form.html.twig")
     */
    public function signupAction(Request $request) {

        $user = new User();
        
        $form = $this->signupForm($user);

        $form->handleRequest($request);
        
        if( $form->isValid() ) {

            $encoder = $this->container->get('security.password_encoder');
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            
            $user->addRole($this->getDoctrine()->getManager()->getRepository('SampleUserBundle:Role')->findOneBy(array('role' => Role::ROLE_USER)));
            
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();
            
            return $this->redirect($this->generateUrl('_login'));
        }

        return array(
            'form' => $form->createView()
        );
        
    }
}
