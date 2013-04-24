<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Symfony\Component\HttpFoundation\Request;

class RequestAwareProvider extends DelegatingProvider
{
    function __construct(Request $request)
    {
        $this->baseUrl = $request->getBaseUrl();
        $this->prefix = $request->getSchemeAndHttpHost() . $this->baseUrl;
        $this->baseUrlLen = strlen($this->baseUrl);
    }


    function url($object, array $options = array())
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