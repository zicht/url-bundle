<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Base class that uses the router for providing urls.
 */
abstract class AbstractRoutingProvider implements Provider
{
    /** @var RouterInterface */
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function url($object, array $options = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        list($name, $params) = $this->routing($object, $options);
        return $this->router->generate(
            $name,
            $params,
            $referenceType
        );
    }

    /**
     * Returns the routing for the object. The return value must be an array of two elements: the route name and the
     * parameters
     *
     * @param mixed $object
     * @param mixed $options
     * @return array
     */
    abstract public function routing($object, array $options = []);
}
