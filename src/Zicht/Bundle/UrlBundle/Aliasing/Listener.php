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

    protected $excludePatterns = array();
    protected $isParamsEnabled = false;
    protected $isQueryStringIgnored = true;

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

    public function setExcludePatterns($excludePatterns)
    {
        $this->excludePatterns = $excludePatterns;
    }

    public function setIsParamsEnabled($isParamsEnabled)
    {
        $this->isParamsEnabled = $isParamsEnabled;
    }

    public function setIsQueryStringIgnored($isQueryStringIgnored)
    {
        $this->isQueryStringIgnored = $isQueryStringIgnored;
    }


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



    public function onKernelRequest(Event\GetResponseEvent $event)
    {
        if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST) {
            $request = $event->getRequest();
            $publicUrl = $request->getRequestUri();

            if ($this->isExcluded($publicUrl)) {
                // don't process urls which are marked as excluded.
                return;
            }

            if ($this->isQueryStringIgnored) {
                if (false !== ($qsStartOffset = strrpos($publicUrl, '?'))) {
                    $publicUrl = substr($publicUrl, 0, $qsStartOffset);
                }
            }
            if ($this->isParamsEnabled) {
                $parts = explode('/', $publicUrl);
                $params = array();
                while (strpos(end($parts), '=') !== false) {
                    array_push($params, array_pop($parts));
                }
                if ($params) {
                    $publicUrl = join('/', $parts);

                    $parser = new \Zicht\Bundle\UrlBundle\Url\Params\UriParser();
                    $request->query->add($parser->parseUri(join('/', array_reverse($params))));

                    if (!$this->aliasing->hasInternalAlias($publicUrl, false)) {
                        $this->routeRequest($event, $publicUrl);

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
                        $event->setResponse(new \Symfony\Component\HttpFoundation\RedirectResponse(
                            $url->getInternalUrl(),
                            $url->getMode()
                        ));
                        break;
                    default:
                        throw new \UnexpectedValueException("Invalid mode {$url->getMode()} for UrlAlias ". json_encode($url));
                }
            }
        }
    }


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
    }
}