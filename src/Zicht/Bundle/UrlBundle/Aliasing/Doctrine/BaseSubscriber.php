<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Doctrine;

use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\Container;

/**
 * Base class for the subscriber implementations
 */
abstract class BaseSubscriber implements EventSubscriber
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var string
     */
    protected $aliaserServiceId;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var array
     */
    protected $records;

    /**
     * Constructor.
     *
     * @param Container $container
     * @param string $aliaserServiceId
     * @param string $className
     * @param boolean $enabled
     */
    public function __construct(Container $container, $aliaserServiceId, $className, $enabled = true)
    {
        // we inject the container because otherwise a circular reference would occur.
        $this->container = $container;
        $this->aliaserServiceId = $aliaserServiceId;
        $this->className = $className;
        $this->enabled = $enabled;
        $this->records = [];
    }

    /**
     * Explicitly set the the subscriber to be enabled or disabled
     *
     * @param string $enabled
     * @return void
     */
    /**
     * Enable or disable the subscriber
     *
     * @param boolean $enabled
     * @return void
     */
    final public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
}
