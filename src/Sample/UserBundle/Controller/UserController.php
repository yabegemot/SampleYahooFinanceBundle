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
use Ob\HighchartsBundle\Highcharts\Highchart;

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
        $user = $this->getUser();
        $stockSymbols = $user->getStockSymbols();
        $symbols = array();
        foreach( $stockSymbols as $stockSymbol )
        {
            $symbols[] = $stockSymbol->getSymbol();
        }
        if( !empty($symbols) )
        {
            $data = array();
            array_walk( $symbols, function ( &$item1, $key ){
                $item1 = urlencode('"'.$item1.'"');
            });
            $implodedSymbols = implode(',',$symbols);

            $date = new \DateTime();
            $c = curl_init();

            for ($d = 0; $d < 24; $d++)
            {
                $dateTo = $date->format('Y-m-j');
                $dateFrom = clone $date;
                $oneDay = new \DateInterval('P1D');
                $oneDay->invert = 1;
                $dateFrom->add($oneDay);
                $dateFrom = $dateFrom->format('Y-m-j');

                $oneMonth = new \DateInterval('P1M');
                $oneMonth->invert = 1;
                $date->add($oneMonth);

                $query = 'http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.historicaldata%20where%20symbol%20in%20('
                        .$implodedSymbols//'%22YHOO%22,%22AAPL%22,%22GOOG%22,%22MSFT%22'
                        .')%20and%20startDate%20=%20%22'
                        .$dateFrom//'2016-11-17'
                        .'%22%20and%20endDate%20=%20%22'
                        .$dateTo//'2016-11-16'
                        .'%22&format=json&env=store://datatables.org/alltableswithkeys';

                curl_setopt($c, CURLOPT_URL, $query);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($c, CURLOPT_HEADER, false);
                $content = curl_exec($c);
                try
                {
                    $contentObject = json_decode($content);
                    if( is_object($contentObject) && isset($contentObject->query->results->quote) )
                    {
                        foreach( $contentObject->query->results->quote as $quote )
                        {
                            if( isset($quote->Symbol) && isset($quote->Date) && isset($quote->Close)
                                && !isset($data[$quote->Symbol][$quote->Date]) )
                            {
                                $data[$quote->Symbol][$quote->Date] = $quote->Close;
                            }
                        }
                    }
                } catch (Exception $ex) {}
            }
        }

        if( !empty($data) )
        {
            $count = 0;
            $series = array();
            $yData = array();
            $categories = array();
            $colors = array('#4572A7', '#AA4643', '#aa9543', '#84b64a', '#4ab6a3', '#4c4ab6', '#964ab6', '#bc4636', '#a8e722', '#a4b189');

            foreach( $data as $symbol => $oneData )
            {
                if( ++$count > 10 )
                {
                    break;
                }
                $oneData = array_reverse($oneData, true);
                array_walk( $oneData, function ( &$item1, $key ){
                    $item1 = round($item1,2);
                });
                $series[] = array(
                    'name'  => $symbol,
                    'type'  => 'spline',
                    'color' => $colors[$count],
                    'yAxis' => 1,
                    'data'  => array_values($oneData),
                );
                $yData[] = array(
                    'labels' => array(
                        'formatter' => new \Zend\Json\Expr('function () { return this.value + "" }'),
                        'style'     => array('color' => $colors[$count])
                    ),
                    'gridLineWidth' => 0,
                    'title' => array(
                        'text'  => '',
                        'style' => array('color' => $colors[$count])
                    ),
                    'opposite' => false,
                );
                $categories = array_keys($oneData);
            }

            $ob = new Highchart();
            $ob->chart->renderTo('container'); // The #id of the div where to render the chart
            $ob->chart->type('column');
            $ob->title->text('Cost of Portfolio vs Time');
            $ob->xAxis->categories($categories);
            $ob->yAxis($yData);
            $ob->legend->enabled(true);
            $formatter = new \Zend\Json\Expr('function () {
                 var unit = {
                 }[this.series.name];
                 return this.x + ": <b>" + this.y + "</b> "/* + unit*/;
             }');
            $ob->tooltip->formatter($formatter);
            $ob->series($series);

            return array('chart' => $ob);
        }
        return array();
    }

    /**
     * User sign up form action
     * 
     * @param Request $request
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

    /**
     * Builds sign form
     * @param User $user
     */
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
     * @param Request $request
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
