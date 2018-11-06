<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\UrlMapperInterface;
use Zicht\Bundle\UrlBundle\Url\Params\UriParser;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

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
                $absolutePrefix = $e->getRequest()->getSchemeAndHttpHost();

                if (parse_url($location, PHP_URL_SCHEME)) {
                    if (substr($location, 0, strlen($absolutePrefix)) === $absolutePrefix) {
                        $relative = substr($location, strlen($absolutePrefix));
                    } else {
                        $relative = null;
                    }
                } else {
                    $relative = $location;
                }

                // Possible suffix for the rewrite URL
                $suffix = '';

                /**
                 * Catches the following situation:
                 *
                 * Some redirect URLs might contain extra URL parameters in the form of:
                 *
                 *      /nl/page/666/terms=FOO/tag=BAR
                 *
                 * (E.g. some SOLR implementations use this URL scheme)
                 *
                 * The relative URL above is then incorrect and the public alias can not be found.
                 *
                 * Remove /terms=FOO/tag=BAR from the relative path and attach to clean URL if found.
                 *
                 */
                if (preg_match('/^(\/[a-z]{2,2}\/page\/\d+)(.*)$/', $relative, $matches)) {
                    list(, $relative, $suffix) = $matches;
                } elseif (preg_match('/^(\/page\/\d+)(.*)$/', $relative, $matches)) {
                    /* For old sites that don't have the locale in the URI */
                    list(, $relative, $suffix) = $matches;
                }

                if (null !== $relative && null !== ($url = $this->aliasing->hasPublicAlias($relative))) {
                    $rewrite = $absolutePrefix . $url . $suffix;
                    $response->headers->set('location', $rewrite);
                }
            }

            $this->rewriteResponse($e->getRequest(), $response);
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
            $publicUrl = rawurldecode($request->getRequestUri());

            if ($this->isExcluded($publicUrl)) {
                // don't process urls which are marked as excluded.
                return;
            }

            if ($this->isParamsEnabled) {
                if (false !== ($queryMark = strpos($publicUrl, '?'))) {
                    $originalUrl = $publicUrl;
                    $publicUrl = substr($originalUrl, 0, $queryMark);
                    $queryString = substr($originalUrl, $queryMark);
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
                        $this->rewriteRequest($event, $publicUrl . $queryString);

                        return;
                    }
                }
            }

            /** @var UrlAlias $url */
            if ($url = $this->aliasing->hasInternalAlias($publicUrl, true)) {
                switch ($url->getMode()) {
                    case UrlAlias::REWRITE:
                        $this->rewriteRequest($event, $url->getInternalUrl());
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
                    $this->rewriteRequest($event, $url->getInternalUrl());

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
    public function rewriteRequest($event, $url)
    {
        // override the request's REQUEST_URI
        $event->getRequest()->initialize(
            $event->getRequest()->query->all(),
            $event->getRequest()->request->all(),
            $event->getRequest()->attributes->all(),
            $event->getRequest()->cookies->all(),
            $event->getRequest()->files->all(),
            array(
                'ORIGINAL_REQUEST_URI' => $event->getRequest()->server->get('REQUEST_URI'),
                'REQUEST_URI' => $url
            ) + $event->getRequest()->server->all(),
            $event->getRequest()->getContent()
        );

        // route the request
        $subEvent = new Event\GetResponseEvent(
            $event->getKernel(),
            $event->getRequest(),
            $event->getRequestType()
        );
        $this->router->onKernelRequest($subEvent);
    }


    /**
     * Rewrite URL's from internal naming to public aliases in the response.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    protected function rewriteResponse(Request $request, Response $response)
    {
        // for debugging purposes. Might need to be configurable.
        if ($request->query->get('__disable_aliasing')) {
            return;
        }
        if (preg_match('!^/admin/!', $request->getRequestUri())) {
            // don't bother here.
            return;
        }

        if ($response->getContent()) {
            $contentType = current(explode(';', $response->headers->get('content-type', 'text/html')));
            $response->setContent(
                $this->aliasing->mapContent(
                    $contentType,
                    UrlMapperInterface::MODE_INTERNAL_TO_PUBLIC,
                    $response->getContent(),
                    [$request->getHttpHost()]
                )
            );
        }
    }
}
