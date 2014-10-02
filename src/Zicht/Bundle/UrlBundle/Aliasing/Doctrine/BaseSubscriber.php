<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Doctrine;

use \Doctrine\Common\EventSubscriber;
use \Symfony\Component\DependencyInjection\Container;

/**
 * Base class for the subscriber implementations
 */
abstract class BaseSubscriber implements EventSubscriber
{
    protected $enabled;

    /**
     * Constructor
     *
     * @param Container $container
     * @param string $aliaserServiceId
     * @param string $className
     */
    public function __construct(Container $container, $aliaserServiceId, $className)
    {
        // this weird construct is needed because otherwise a circular reference would occur in the container.
        $this->container = $container;
        $this->aliaserServiceId = $aliaserServiceId;
        $this->className = $className;

        $this->records = array();

        $this->enabled = true;
    }


    final public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
}