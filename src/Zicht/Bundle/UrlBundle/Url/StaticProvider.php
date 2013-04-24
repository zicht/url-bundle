<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Symfony\Component\Routing\RouterInterface;

class StaticProvider implements Provider
{
    /**
     * Create the provider with a set of static references, i.e. mappings from name to url.
     *
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param array $refs
     */
    public function __construct(RouterInterface $router, array $refs = array())
    {
        $this->refs = $refs;
        $this->router = $router;
    }


    /**
     * Add the array as references
     *
     * @param array $refs
     */
    function addAll(array $refs)
    {
        $this->refs = $refs + $this->refs;
    }


    /**
     * Add a single reference
     *
     * @param string $name
     * @param string $value
     */
    function add($name, $value)
    {
        $this->refs[$name] = $value;
    }


    /**
     * Must return true if the current provider matches the object, i.e. knows how to generate a URL for the passed
     * object.
     *
     * @param $object
     * @return mixed
     */
    function supports($object)
    {
        return is_string($object) && isset($this->refs[$object]);
    }

    /**
     * @{inheritDoc}
     */
    function url($object, array $options = array())
    {
        return $this->router->getContext()->getBaseUrl() . '/' . ltrim($this->refs[$object], '/');
    }
}