<?php

namespace Sample\YahooFinanceBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sample\UserBundle\Form\UserPortfolioType;
use Sample\YahooFinanceBundle\Entity\StockSymbol;

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
        $user = $this->getUser();

        $form = $this->createForm(new UserPortfolioType($this->container), $user, array(
            'action' => $this->generateUrl('build_portfolio'),
            'method' => 'POST',
            'attr' => array(),
        ));
        $form->add('submit', 'submit', array('label' => 'Build Portfolio', 'attr' => array('class' => 'btn btn-primary')));

        return array('form' => $form->createView());
    }

    /**
     *
     * @param Request $request
     * @Route("/build_portfolio", name="build_portfolio")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     * @Template("SampleYahooFinanceBundle:Finance:index.html.twig")
     */
    public function buildPortfolioAction(Request $request)
    {
        $user = $this->getUser();
        $wasStockSymbols = clone $user->getStockSymbols();
        
        $form = $this->createForm(new UserPortfolioType($this->container), $user, array(
            'action' => $this->generateUrl('build_portfolio'),
            'method' => 'POST',
            'attr' => array(),
        ));
        $form->add('submit', 'submit', array('label' => 'Build Portfolio', 'attr' => array('class' => 'btn btn-primary')));
        
        $form->handleRequest($request);

        if( $form->isValid() )
        {
            $em = $this->getDoctrine()->getManager();
            foreach( $user->getStockSymbols() as $stockSymbol )
            {
                if( !$stockSymbol->hasUser($user) )
                {
                    $stockSymbol->addUser($user);
                }
                $em->persist($stockSymbol);
            }

            foreach( $wasStockSymbols as $wasStockSymbol )
            {
                if( !$user->hasStockSymbol($wasStockSymbol) )
                {
                    $wasStockSymbol->removeUser($user);
                    $em->persist($wasStockSymbol);
                }
            }
            $em->persist($user);
            $em->flush();
            return $this->redirect($this->generateUrl('finance_home'));
        }
        return array('form' => $form->createView());
    }

    /**
     *
     * @param Request $request
     * @Route("/remove_symbol", name="remove_symbol", options={"expose"=true})
     * @Method("DELETE")
     * @Security("has_role('ROLE_USER')")
     */
    public function removeSymbolAction(Request $request)
    {
        $response = new \stdClass();
        $response->valid = false;
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $stockSymbolRepository = $em->getRepository('SampleYahooFinanceBundle:StockSymbol');

        if( !empty($id) && ($stockSymbol = $stockSymbolRepository->find($id)) )
        {
            $user = $this->getUser();
            if( $user->hasStockSymbol($stockSymbol)
                && $stockSymbol->hasUser($user) )
            {
                $user->removeStockSymbol($stockSymbol);
                $stockSymbol->removeUser($user);
            }
            if( $user->hasPortfolioSymbol($stockSymbol)
                && $stockSymbol->hasPortfolioUser($user) )
            {
                $user->removePortfolioSymbol($stockSymbol);
                $stockSymbol->removeUserPortfolio($user);
            }
            $em->persist($user);
            $em->persist($stockSymbol);
            $em->flush();
            $response->valid = true;
            
        }
        
        return new JsonResponse($response);
    }

    /**
     *
     * @param Request $request
     * @Route("/lookup_symbol", name="lookup_symbol")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     * @Template("SampleYahooFinanceBundle:Finance:index.html.twig")
     */
    public function lookupSymbolAction(Request $request)
    {
        $symbol = $request->get('symbol');
                    
        if( !empty($symbol) )
        {
            $em = $this->getDoctrine()->getManager();
            $stockSymbolRepository = $em->getRepository('SampleYahooFinanceBundle:StockSymbol');

            if( !($stockSymbol = $stockSymbolRepository->findOneBy(array('symbol' => $symbol))) )
            {
                $query = 'select * from yahoo.finance.quotes where symbol in ("'.$symbol.'")';
                $query = 'https://query.yahooapis.com/v1/public/yql?q='
                    .urlencode($query)
                    .'&format=json&env=store://datatables.org/alltableswithkeys&callback=';
                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, $query);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($c, CURLOPT_HEADER, false);
                $content = curl_exec($c);
                try
                {
                    $contentObject = json_decode($content);

                    if( is_object($contentObject) && !empty($contentObject->query->results->quote->Name) )
                    {
                        if( !$stockSymbolRepository->findOneBy(array('symbol' => $symbol)) )
                        {
                            $stockSymbol = new StockSymbol();
                            $stockSymbol->setSymbol($symbol);
                            $em->persist($stockSymbol);
                            $em->flush();
                        }
                    }
                } catch (Exception $ex) {}
            }

            $user = $this->getUser();
            if( $stockSymbol instanceof StockSymbol
                && !$user->hasPortfolioSymbol($stockSymbol)
                && !$stockSymbol->hasPortfolioUser($user) )
            {
                    $user->addPortfolioSymbol($stockSymbol);
                    $em->persist($user);
                    $stockSymbol->addUserPortfolio($user);
                    $em->persist($stockSymbol);
                    $em->flush();
            }
        }

        return $this->redirect($this->generateUrl('finance_home'));
    }
}
