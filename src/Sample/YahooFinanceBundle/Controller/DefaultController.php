<?php

namespace Sample\YahooFinanceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('SampleYahooFinanceBundle:Default:index.html.twig');
    }
}
