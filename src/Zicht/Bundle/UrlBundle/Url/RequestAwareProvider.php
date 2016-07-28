<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Symfony\Component\HttpFoundation\Request;

/**
 * This provider is "request aware", so it can either render absolute URL's
 */
class RequestAwareProvider extends DelegatingProvider
{
    /**
     * Constructor
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct();

        $this->baseUrl = $request->getBaseUrl();
        $this->prefix = $request->getSchemeAndHttpHost() . $this->baseUrl;
        $this->baseUrlLen = strlen($this->baseUrl);
    }

    /**
     * @{inheritDoc}
     */
    public function url($object, array $options = array())
    {
        $ret = parent::url($object, $options);

        if ($this->baseUrlLen && substr($ret, 0, $this->baseUrlLen) == $this->baseUrl) {
            $ret = substr($ret, $this->baseUrlLen);
        }
        $ret = ltrim($ret, '/');
        if (!empty($options['absolute'])) {
            $ret = $this->prefix . '/' . $ret;
        }
        return $ret;
    }
}
