<?php

namespace Sample\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Description of SecurityController
 *
 * @author Yuriy Arutyunov <yabegemot@gmail.com>
 * @Route("/")
 */
class SecurityController extends Controller {

    /**
     *
     * @Route("/", name="_login")
     * @Method("GET")
     */
    public function loginAction(Request $request) {
        $session = $request->getSession();
        
        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR))
        {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        }
        else
        {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }



        if (is_string($error) && strpos($error, "AuthenticationServiceException"))
        {
            $error = array("message" => "Server error");
        }

        $response = $this->render('SampleUserBundle:Security:login.html.twig', array(
            // last username entered by the user
            'last_username' => '',
            'error' => $error,
                )
        );

        //Clear remaining user if existent after the response is rendered.
        $secret = $this->container->getParameter('kernel.secret');
        $token = new AnonymousToken($secret, 'anon.');

        $this->get('security.token_storage')->setToken($token);
        $session->invalidate();

        return $response;
    }

    /**
     * @Route("/login_check", name="_security_check")
     */
    public function securityCheckAction() {
        // The security layer will intercept this request
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction() {
        // The security layer will intercept this request
    }

}
