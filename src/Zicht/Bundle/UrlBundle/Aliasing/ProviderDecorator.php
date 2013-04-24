<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
 
namespace Zicht\Bundle\UrlBundle\Aliasing;

use \Zicht\Bundle\UrlBundle\Url\DelegatingProvider;

class ProviderDecorator extends DelegatingProvider
{
    function __construct(Aliasing $aliasing)
    {
        $this->aliasing = $aliasing;
    }


    function url($object, array $options = array())
    {
        $ret = parent::url($object, $options);
        if ($publicUrl = $this->aliasing->hasPublicAlias($ret)) {
            $ret = $publicUrl;
        }
        return $ret;
    }
}