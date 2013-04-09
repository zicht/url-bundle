<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Logging;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zicht\Bundle\UrlBundle\Logging\Logging;

class Listener
{
    /**
     * @var \Zicht\Bundle\UrlBundle\Entity\ErrorLog
     */
    protected $log = null;

    public function __construct(Logging $logging)
    {
        $this->logging = $logging;
    }


    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $this->log = $this->logging->createLog($event->getRequest(), $event->getException()->getMessage());
    }


    public function onKernelResponse(\Symfony\Component\HttpKernel\Event\FilterResponseEvent $e)
    {
        if ($e->getRequestType() === HttpKernelInterface::MASTER_REQUEST && ($status = $e->getResponse()->getStatusCode()) >= 400) {
            if (isset($this->log)) {
                $this->log->status = $status;
                $this->logging->flush($this->log);
            }
        }
    }
}