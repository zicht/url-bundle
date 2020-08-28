<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Logging;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Listener used for logging
 */
class Listener
{
    /**
     * @var \Zicht\Bundle\UrlBundle\Entity\ErrorLog
     */
    protected $log = null;

    /**
     * Construct with the passed logger service
     *
     * @param Logging $logging
     */
    public function __construct(Logging $logging)
    {
        $this->logging = $logging;
    }


    /**
     * Create log entry if a kernelexception occurs.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     * @return void
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $this->log = $this->logging->createLog($event->getRequest(), $event->getException()->getMessage());
    }

    /**
     * Save the log entry (if any) if the error response about to be sent is not handled otherwise.
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $e
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $e)
    {
        if (isset($this->log)) {
            if ($e->getRequestType() === HttpKernelInterface::MASTER_REQUEST) {
                if (($status = $e->getResponse()->getStatusCode()) >= 400) {
                    $this->log->setStatus($status);
                    $this->logging->flush($this->log);
                }
            }
        }
    }
}
