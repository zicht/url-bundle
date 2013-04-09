<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use \Symfony\Component\HttpKernel\Event;
use \Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use \Symfony\Component\HttpKernel\EventListener\RouterListener;
use \Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Listens to incoming and outgoing requests to handle url aliasing at the kernel master request level.
 */
class Listener
{
    protected $aliasing;

    /**
     * Construct the aliasing listener.
     *
     * @param Aliasing $aliasing
     * @param RouterListener $router
     */
    public function __construct(Aliasing $aliasing, RouterListener $router)
    {
        $this->aliasing = $aliasing;
        $this->router = $router;
    }


    /**
     * Listens to redirect responses, to replace any internal url with a public one.
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $e
     */
    public function onKernelResponse(Event\FilterResponseEvent $e)
    {
        if ($e->getRequestType() === HttpKernelInterface::MASTER_REQUEST) {
            $response = $e->getResponse();

            // only do anything if the response has a Location header
            if (false !== ($location = $response->headers->get('location', false))) {
                $req = $e->getRequest()->getSchemeAndHttpHost();
                if (substr($location, 0, strlen($req)) === $req) {
                    $relative = substr($location, strlen($req));

                    if ($url = $this->aliasing->hasPublicAlias($relative)) {
                        $rewrite = $req . $url;
                        $response->headers->set('location', $rewrite);
                        $response->setContent(str_replace($location, $rewrite, $response->getContent()));
                    }
                }
            }
        }
    }


    public function onKernelRequest(Event\GetResponseEvent $event)
    {
        if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST) {
            $publicUrl = $event->getRequest()->getRequestUri();


            /** @var UrlAlias $url */
            if ($url = $this->aliasing->hasInternalAlias($publicUrl, true)) {
                switch ($url->mode) {
                    case UrlAlias::REWRITE:
                        $duplicate = $event->getRequest()->duplicate(
                            null,
                            null,
                            null,
                            null,
                            null,
                            array('REQUEST_URI' => $url->internal_url)
                        );

                        $subEvent = new Event\GetResponseEvent($event->getKernel(), $duplicate, $event->getRequestType());
                        $this->router->onKernelRequest($subEvent);
                        $event->getRequest()->attributes = $duplicate->attributes;
                        break;
                    case UrlAlias::MOVE:
                    case UrlAlias::ALIAS:
                        $event->setResponse(new \Symfony\Component\HttpFoundation\RedirectResponse(
                            $url->internal_url,
                            $url->mode
                        ));
                        break;
                    default:
                        throw new \UnexpectedValueException("Invalid mode {$url->mode} for UrlAlias ". json_encode($url));
                }
            }
        }
    }
}