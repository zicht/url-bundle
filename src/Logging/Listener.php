<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Logging;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zicht\Bundle\UrlBundle\Entity\ErrorLog;

/**
 * Listener used for logging
 */
class Listener
{
    /** @var Logging */
    private $logging;

    /** @var ErrorLog|null */
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
     * @param ExceptionEvent $event
     * @return void
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $this->log = $this->logging->createLog($event->getRequest(), $event->getThrowable()->getMessage());
    }

    /**
     * Save the log entry (if any) if the error response about to be sent is not handled otherwise.
     *
     * @param ResponseEvent $e
     * @return void
     */
    public function onKernelResponse(ResponseEvent $e)
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
