<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Url\DbStaticProvider;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Zicht\Bundle\UrlBundle\Url\DbStaticProvider;

/**
 * Sets the master request on the dbstatic provider
 */
class RequestListener
{
    /**
     * Constructor
     *
     * @param \Zicht\Bundle\UrlBundle\Url\DbStaticProvider $provider
     */
    public function __construct(DbStaticProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @{inheritDoc}
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST) {
            $this->provider->setRequest($event->getRequest());
        }
    }
}
