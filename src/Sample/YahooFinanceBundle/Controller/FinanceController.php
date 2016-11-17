<?php

namespace Sample\YahooFinanceBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of FinanceController
 *
 * @author Yuriy Arutyunov <yabegemot@gmail.com>
 * @Route("/finance")
 */
class FinanceController extends Controller
{
    /**
     *
     * @Route("/", name="finance_home")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     * @Template("SampleYahooFinanceBundle:Finance:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }
}
