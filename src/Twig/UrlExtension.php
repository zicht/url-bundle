<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Exception\UnsupportedException;
use Zicht\Bundle\UrlBundle\Url\Provider;
use Zicht\Bundle\UrlBundle\Url\ShortUrlManager;

/**
 * Provides some twig utilities.
 */
class UrlExtension extends AbstractExtension
{
    /** @var Provider */
    protected $provider;

    /** @var ShortUrlManager */
    private $shortUrlManager;

    /** @var Aliasing|null */
    protected $aliasing;

    /** @var array */
    private $static_refs = [];

    /**
     * Construct the extension with the passed object as provider. The provider is typically a DelegatingProvider
     * that delegates to all registered url providers.
     *
     * @param Aliasing|null $aliasing
     */
    public function __construct(Provider $provider, ShortUrlManager $shortUrlManager, $aliasing = null)
    {
        $this->provider = $provider;
        $this->shortUrlManager = $shortUrlManager;
        $this->aliasing = $aliasing;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('internal_to_public_aliasing', [$this, 'internalToPublicAliasing']),
        ];
    }

    /**
     * Takes a HTML sting and replaces all internal urls with aliased public urls, i.e. /nl/page/42 -> /nl/bring-your-towel
     *
     * @param string $html
     * @return string
     *
     * @deprecated Should no longer be used, the aliasing is now handled by a response listener.
     */
    public function internalToPublicAliasing($html)
    {
        return $html;
    }

    public function getFunctions()
    {
        return [
            'object_url' => new TwigFunction('object_url', [$this, 'objectUrl']),
            'static_ref' => new TwigFunction('static_ref', [$this, 'staticRef']),
            'static_reference' => new TwigFunction('static_reference', [$this, 'staticRef']),
            'short_url' => new TwigFunction('short_url', [$this, 'shortUrl']),
        ];
    }

    /**
     * Returns an url based on the passed object.
     *
     * @param object $object
     * @param mixed $defaultIfNotFound
     * @return string
     */
    public function objectUrl($object, $defaultIfNotFound = null)
    {
        try {
            $ret = $this->provider->url($object);
        } catch (UnsupportedException $e) {
            if (null === $defaultIfNotFound) {
                throw $e;
            } else {
                if (true === $defaultIfNotFound) {
                    $ret = (string)$object;
                } else {
                    $ret = $defaultIfNotFound;
                }
            }
        }
        return $ret;
    }

    /**
     * Returns a static reference, i.e. an url that is provided based on a simple string.
     *
     * @param string $name
     * @param array $params
     *
     * @return string
     */
    public function staticRef($name, $params = null)
    {
        $name = (string)$name;
        if (!isset($this->static_refs[$name])) {
            try {
                $this->static_refs[$name] = $this->provider->url($name);
            } catch (UnsupportedException $e) {
                $this->static_refs[$name] = '/[static_reference: ' . $name . ']';
            }
        }

        $ret = $this->static_refs[$name];
        if ($params) {
            $ret .= '?' . http_build_query($params, 0, '&');
        }

        return $ret;
    }

    /**
     * @param string $originatingUrl
     * @param string|null $prefix
     * @param int $minLength
     * @return string
     */
    public function shortUrl($originatingUrl, $prefix = null, $minLength = 8)
    {
        $alias = $this->shortUrlManager->getAlias($originatingUrl, $prefix, $minLength);
        return $alias->getPublicUrl();
    }
}
