<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use \Symfony\Component\HttpKernel\Event;
use \Symfony\Component\HttpFoundation\RedirectResponse;
use \Zicht\Bundle\UrlBundle\Url\Params\UriParser;
use \Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use \Symfony\Component\HttpKernel\EventListener\RouterListener;
use \Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Listens to incoming and outgoing requests to handle url aliasing at the kernel master request level.
 */
class Listener
{
    protected $aliasing;

    protected $excludePatterns = array();
    protected $isParamsEnabled = false;

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
     * @return void
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

    /**
     * Exclude patterns from aliasing
     *
     * @param array $excludePatterns
     * @return void
     */
    public function setExcludePatterns($excludePatterns)
    {
        $this->excludePatterns = $excludePatterns;
    }

    /**
     * Whether or not to consider URL parameters (key/value pairs at the end of the URL)
     *
     * @param bool $isParamsEnabled
     * @return void
     */
    public function setIsParamsEnabled($isParamsEnabled)
    {
        $this->isParamsEnabled = $isParamsEnabled;
    }

    /**
     * Returns true if the URL matches any of the exclude patterns
     *
     * @param string $url
     * @return bool
     */
    protected function isExcluded($url)
    {
        $ret = false;
        foreach ($this->excludePatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                $ret = true;
                break;
            }
        }
        return $ret;
    }


    /**
     * Listens to master requests and translates the URL to an internal url, if there is an alias available
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @return void
     * @throws \UnexpectedValueException
     */
    public function onKernelRequest(Event\GetResponseEvent $event)
    {
        if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST) {
            $request = $event->getRequest();
            $publicUrl = $request->getRequestUri();

            if ($this->isExcluded($publicUrl)) {
                // don't process urls which are marked as excluded.
                return;
            }

            if ($this->isParamsEnabled) {
                if (false !== ($queryMark = strpos($publicUrl, '?'))) {
                    $originalUrl = $publicUrl;
                    $publicUrl = substr($originalUrl, 0, $queryMark);
                    $queryString = substr($originalUrl, $queryMark);;
                } else {
                    $queryString = null;
                }

                $parts = explode('/', $publicUrl);
                $params = array();
                while (false !== strpos(end($parts), '=')) {
                    array_push($params, array_pop($parts));
                }
                if ($params) {
                    $publicUrl = join('/', $parts);

                    $parser = new UriParser();
                    $request->query->add($parser->parseUri(join('/', array_reverse($params))));

                    if (!$this->aliasing->hasInternalAlias($publicUrl, false)) {
                        $this->routeRequest($event, $publicUrl . $queryString);

                        return;
                    }
                }
            }

            /** @var UrlAlias $url */
            if ($url = $this->aliasing->hasInternalAlias($publicUrl, true)) {
                switch ($url->getMode()) {
                    case UrlAlias::REWRITE:
                        $this->routeRequest($event, $url->getInternalUrl());
                        break;
                    case UrlAlias::MOVE:
                    case UrlAlias::ALIAS:
                        $event->setResponse(new RedirectResponse($url->getInternalUrl(), $url->getMode()));
                        break;
                    default:
                        throw new \UnexpectedValueException(
                            sprintf(
                                "Invalid mode %s for UrlAlias %s.",
                                $url->getMode(),
                                json_encode($url)
                            )
                        );
                }
            } elseif (strpos($publicUrl, '?') !== false) {
                // allow aliases to receive the query string.

                $publicUrl = substr($publicUrl, 0, strpos($publicUrl, '?'));
                if ($url = $this->aliasing->hasInternalAlias($publicUrl, true, UrlAlias::REWRITE)) {
                    $this->routeRequest($event, $url->getInternalUrl());

                    return;
                }
            }
        }
    }


    /**
     * Route the request to the specified URL.
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @param string $url
     * @return void
     */
    public function routeRequest($event, $url)
    {
        $duplicate = $event->getRequest()->duplicate(
            null,
            null,
            null,
            null,
            null,
            array('REQUEST_URI' => $url)
        );

        $subEvent = new Event\GetResponseEvent($event->getKernel(), $duplicate, $event->getRequestType());
        $this->router->onKernelRequest($subEvent);
        $event->getRequest()->attributes = $duplicate->attributes;
        $event->getRequest()->attributes->set('_internal_url', $url);
        $event->getRequest()->setRequestFormat($duplicate->get('_format'));
    }
}