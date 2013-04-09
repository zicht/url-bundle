<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Symfony\Component\HttpFoundation\Request;

class RelativeProvider extends DelegatingProvider
{
    function __construct(Request $request)
    {
        $this->baseUrl = $request->getBaseUrl();
        $this->baseUrlLen = strlen($this->baseUrl);
    }


    function url($object)
    {
        $ret = parent::url($object);

        if ($this->baseUrlLen && substr($ret, 0, $this->baseUrlLen) == $this->baseUrl) {
            $ret = substr($ret, $this->baseUrlLen);
        }
        $ret = ltrim($ret, '/');
        return $ret;
    }
}