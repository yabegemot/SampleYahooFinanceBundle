<?php

namespace AppBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\DependencyInjection\Container;
use Twig_Extension;
use Twig_Function_Function;
use Twig_SimpleFilter;

class AppExtension extends Twig_Extension {

    /**
     * @var Container $container
     */
    protected $container;
    protected $doctrine;

    public function __construct(Container $container, RegistryInterface $doctrine) {
        $this->container = $container;
        $this->doctrine = $doctrine;
    }

    public function getFilters() {
        return array(
            new Twig_SimpleFilter('count', array($this, '_count')),
            new Twig_SimpleFilter('empty', array($this, 'is_empty')),
            new Twig_SimpleFilter('isset', array($this, 'is_set')),
            new Twig_SimpleFilter('isnull', array($this, 'is_null')),
            new Twig_SimpleFilter('isarray', array($this, 'is_array')),
            new Twig_SimpleFilter('is_numeric', array($this, 'is_numeric')),
        );
    }

    public function getFunctions() {
        return array(
            'call_static' => new Twig_Function_Function(array($this, 'call_static')),
        );
    }

    public function _count($var) {
        return count($var);
    }

    public function is_empty($sentence) {
        return empty($sentence);
    }

    public function is_set($var) {
        return isset($var);
    }

    public function is_null($var) {
        return $var === null;
    }

    public function is_array($var) {
        return is_array($var);
    }

    public function is_numeric($var) {
        return is_numeric($var);
    }

    function call_static($class, $function, $args = array()) {
        if (class_exists($class) && method_exists($class, $function)) {
            return call_user_func_array(array($class, $function), $args);
        }
        return null;
    }
}
