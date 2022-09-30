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
    /** @var array */
    protected $refs;

    /** @var RouterInterface */
    protected $router;

    /**
     * Create the provider with a set of static references, i.e. mappings from name to url.
     */
    public function __construct(RouterInterface $router, array $refs = [])
    {
        $this->refs = $refs;
        $this->router = $router;
    }

    /**
     * Add the array as references
     *
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

    public function supports($object)
    {
        return is_string($object) && isset($this->refs[$object]);
    }

    public function url($object, array $options = [])
    {
        return $this->router->getContext()->getBaseUrl() . '/' . ltrim($this->refs[$object], '/');
    }
}
