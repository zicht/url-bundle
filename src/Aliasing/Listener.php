<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\UrlMapperInterface;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Url\Params\UriParser;

/**
 * Listens to incoming and outgoing requests to handle url aliasing at the kernel master request level.
 */
class Listener
{
    const SLASH_SUFFIX_IGNORE = 'ignore';
    const SLASH_SUFFIX_ACCEPT = 'accept';
    const SLASH_SUFFIX_REDIRECT_PERM = 'redirect-301';
    const SLASH_SUFFIX_REDIRECT_TEMP = 'redirect-302';

    /** @var Aliasing */
    protected $aliasing;

    /** @var RouterListener */
    protected $router;

    /** @var string[] */
    protected $excludePatterns = [];

    /** @var bool */
    protected $isParamsEnabled = false;

    /** @var string|null */
    protected $slashSuffixHandling = self::SLASH_SUFFIX_IGNORE;

    /**
     * Construct the aliasing listener.
     */
    public function __construct(Aliasing $aliasing, RouterListener $router)
    {
        $this->aliasing = $aliasing;
        $this->router = $router;
    }

    /**
     * Listens to redirect responses, to replace any internal url with a public one.
     *
     * @return void
     */
    public function onKernelResponse(Event\ResponseEvent $event)
    {
        if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST) {
            $response = $event->getResponse();

            // only do anything if the response has a Location header
            $location = $response->headers->get('location', false);
            if (false !== $location) {
                $absolutePrefix = $event->getRequest()->getSchemeAndHttpHost();

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

                /*
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
                    [, $relative, $suffix] = $matches;
                } elseif (preg_match('/^(\/page\/\d+)(.*)$/', $relative, $matches)) {
                    /* For old sites that don't have the locale in the URI */
                    [, $relative, $suffix] = $matches;
                }

                if (null !== $relative) {
                    if (null !== ($url = $this->aliasing->hasPublicAlias($relative))) {
                        $rewrite = $absolutePrefix . $url . $suffix;
                        $response->headers->set('location', $rewrite);
                    }
                }
            }

            $this->rewriteResponse($event->getRequest(), $response);
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
     * @param string $slashSuffixHandling
     */
    public function setSlashSuffixHandling($slashSuffixHandling)
    {
        $this->slashSuffixHandling = $slashSuffixHandling;
    }

    /**
     * Returns true if the URL matches any of the exclude patterns
     *
     * @param string $url
     * @return bool
     */
    protected function isExcluded($url)
    {
        foreach ($this->excludePatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Listens to master requests and translates the URL to an internal url, if there is an alias available
     *
     * @return void
     * @throws \UnexpectedValueException
     */
    public function onKernelRequest(Event\RequestEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $publicUrl = rawurldecode($request->getRequestUri());

        if ($this->isExcluded($publicUrl)) {
            // don't process urls which are marked as excluded.
            return;
        }

        $queryString = '';
        if (false !== ($queryMark = strpos($publicUrl, '?'))) {
            $queryString = substr($publicUrl, $queryMark);
            $publicUrl = substr($publicUrl, 0, $queryMark);
        }

        if ($this->isParamsEnabled) {
            $parts = explode('/', $publicUrl);
            $params = [];
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

        $tryPublicUrls = [$publicUrl => null];
        if ($queryString !== '') {
            $tryPublicUrls[$publicUrl . $queryString] = null;
        }
        if ($this->slashSuffixHandling !== static::SLASH_SUFFIX_IGNORE && substr($publicUrl, -1) === '/' && rtrim($publicUrl, '/') !== '') {
            $tryPublicUrls[rtrim($publicUrl, '/')] = $this->slashSuffixHandling;
            if ($queryString !== '') {
                $tryPublicUrls[rtrim($publicUrl, '/') . $queryString] = $this->slashSuffixHandling;
            }
        }

        $qb = $this->aliasing->getRepository()->createQueryBuilder('u');
        $qb->where($qb->expr()->in('u.public_url', array_keys($tryPublicUrls)))
            ->indexBy('u', 'u.public_url');
        /** @var UrlAlias[] $urlAliases */
        $urlAliases = $qb->getQuery()->getResult();

        if (count($urlAliases) === 0) {
            return;
        }

        foreach ($tryPublicUrls as $tryPublicUrl => $handlingMode) {
            if (!array_key_exists($tryPublicUrl, $urlAliases)) {
                continue;
            }

            $urlAlias = $urlAliases[$tryPublicUrl];

            switch ($handlingMode) {
                case static::SLASH_SUFFIX_REDIRECT_TEMP:
                    $url = $urlAlias->getMode() === UrlAlias::ALIAS ? $urlAlias->getInternalUrl() : $urlAlias->getPublicUrl(); // Same mode? Use internal URL directly, don't go redirecting twice...
                    $event->setResponse(new RedirectResponse($url, Response::HTTP_FOUND));
                    break;

                case static::SLASH_SUFFIX_REDIRECT_PERM:
                    $url = $urlAlias->getMode() === UrlAlias::MOVE ? $urlAlias->getInternalUrl() : $urlAlias->getPublicUrl(); // Same mode? Use internal URL directly, don't go redirecting twice...
                    $event->setResponse(new RedirectResponse($url, Response::HTTP_MOVED_PERMANENTLY));
                    break;

                case static::SLASH_SUFFIX_ACCEPT:
                    // Continue as if the correct URL (without '/' suffix) was requested. Could result in duplicate content disqualifications
                default: // $handlingMode === null
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
                break;
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
            [
                'ORIGINAL_REQUEST_URI' => $event->getRequest()->server->get('REQUEST_URI'),
                'REQUEST_URI' => $url,
            ] + $event->getRequest()->server->all(),
            $event->getRequest()->getContent()
        );

        // route the request
        $subEvent = new Event\RequestEvent(
            $event->getKernel(),
            $event->getRequest(),
            $event->getRequestType()
        );
        $this->router->onKernelRequest($subEvent);
    }

    /**
     * Rewrite URL's from internal naming to public aliases in the response.
     *
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
