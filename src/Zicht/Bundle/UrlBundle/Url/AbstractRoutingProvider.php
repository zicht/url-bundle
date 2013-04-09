<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use \Symfony\Component\Routing\RouterInterface;

abstract class AbstractRoutingProvider implements Provider
{
    function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }


    /**
     * Returns the URL for the object. Should throw a NotSupportedException if the passed object is not supported.
     *
     * @param $object
     * @return mixed
     */
    function url($object)
    {
        list($name, $params) = $this->routing($object);
        return $this->router->generate(
            $name,
            $params
        );
    }


    /**
     * Returns the routing for the object. The return value must be an array of two elements: the route name and the
     * parameters
     *
     * @param mixed $object
     * @return array
     */
    abstract function routing($object);
}