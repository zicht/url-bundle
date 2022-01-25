<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This provider is "request aware", so it can either render absolute URL's
 */
class RequestAwareProvider extends DelegatingProvider
{
    /** @var string|null */
    private $baseUrl;

    /** @var string */
    private $prefix;

    /** @var int */
    private $baseUrlLen;

    public function __construct(RequestStack $requestStack)
    {
        parent::__construct();

        $request = $requestStack->getMasterRequest();

        $this->baseUrl = $request->getBaseUrl();
        $this->prefix = $request->getSchemeAndHttpHost() . $this->baseUrl;
        $this->baseUrlLen = strlen($this->baseUrl);
    }

    /**
     * {@inheritDoc}
     */
    public function url($object, array $options = [])
    {
        $ret = parent::url($object, $options);

        if ($this->baseUrlLen && substr($ret, 0, $this->baseUrlLen) === $this->baseUrl) {
            $ret = substr($ret, $this->baseUrlLen);
        }
        $ret = ltrim($ret, '/');
        if (!empty($options['absolute'])) {
            $ret = $this->prefix . '/' . $ret;
        }
        return $ret;
    }
}
