<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\UrlMapperInterface;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * Listens to incoming and outgoing requests to handle url aliasing at the kernel master request level.
 */
class Listener
{
    /** @var Aliasing */
    protected $aliasing;

    /** @var RouterListener */
    protected $routerListener;

    /** @var PublicAliasHandler */
    private $publicAliasHandler;

    /**
     * Construct the aliasing listener.
     *
     * @param Aliasing $aliasing
     * @param RouterListener $router
     */
    public function __construct(Aliasing $aliasing, RouterListener $router, PublicAliasHandler $publicAliasHandler)
    {
        $this->aliasing = $aliasing;
        $this->routerListener = $router;
        $this->publicAliasHandler = $publicAliasHandler;
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
     * Listens to master requests and translates the URL to an internal url, if there is an alias available
     *
     * @param GetResponseEvent $event
     * @return void
     * @throws \UnexpectedValueException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $requestUri = $request->getRequestUri();
        if (false !== ($queryMarkPos = strpos($requestUri, '?'))) {
            $publicUrl = rawurldecode(substr($requestUri, 0, $queryMarkPos)) . substr($requestUri, $queryMarkPos);
        } else {
            $publicUrl = rawurldecode($requestUri);
        }

        if (!($urlAlias = $this->publicAliasHandler->handlePublicUrl($publicUrl))) {
            return;
        }

        switch ($urlAlias->getMode()) {
            case UrlAlias::REWRITE:
                $this->rewriteRequest($event, $urlAlias->getInternalUrl());
                break;
            case UrlAlias::MOVE:
            case UrlAlias::ALIAS:
                $event->setResponse(new RedirectResponse($urlAlias->getInternalUrl(), $urlAlias->getMode()));
                break;
            default:
                throw new \UnexpectedValueException(sprintf("Invalid mode %s for UrlAlias %s.", $urlAlias->getMode(), json_encode($urlAlias)));
        }
    }

    /**
     * Route the request to the specified URL.
     *
     * @param GetResponseEvent $event
     * @param string $url
     */
    public function rewriteRequest($event, $url)
    {
        // override the request's REQUEST_URI
        // @todo get query params off of the $url
        $event->getRequest()->initialize(
            $event->getRequest()->query->all(),
            $event->getRequest()->request->all(),
            $event->getRequest()->attributes->all(),
            $event->getRequest()->cookies->all(),
            $event->getRequest()->files->all(),
            [
                'ORIGINAL_REQUEST_URI' => $event->getRequest()->server->get('REQUEST_URI'),
                'REQUEST_URI' => $url
            ] + $event->getRequest()->server->all(),
            $event->getRequest()->getContent()
        );

        // route the request
        $subEvent = new GetResponseEvent(
            $event->getKernel(),
            $event->getRequest(),
            $event->getRequestType()
        );
        $this->routerListener->onKernelRequest($subEvent);
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
