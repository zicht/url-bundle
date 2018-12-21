<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Symfony\Component\Routing\RouterInterface;

/**
 * Static provider holds a set of urls
 */
class StaticProvider implements Provider
{
    /**
     * Create the provider with a set of static references, i.e. mappings from name to url.
     *
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param array $refs
     */
    public function __construct(RouterInterface $router, array $refs = [])
    {
        $this->refs = $refs;
        $this->router = $router;
    }

    /**
     * Add the array as references
     *
     * @param array $refs
     * @return void
     */
    public function addAll(array $refs)
    {
        $this->refs = $refs + $this->refs;
    }

    /**
     * Add a single reference
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function add($name, $value)
    {
        $this->refs[$name] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return is_string($object) && isset($this->refs[$object]);
    }

    /**
     * {@inheritdoc}
     */
    public function url($object, array $options = [])
    {
        return $this->router->getContext()->getBaseUrl() . '/' . ltrim($this->refs[$object], '/');
    }
}
