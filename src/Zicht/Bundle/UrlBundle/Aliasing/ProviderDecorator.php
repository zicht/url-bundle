<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
 
namespace Zicht\Bundle\UrlBundle\Aliasing;

use Zicht\Bundle\UrlBundle\Url\DelegatingProvider;

/**
 * Decorator for aliasing. No longer used, may be removed in next major release.
 *
 * @deprecated The provider decorator and it's aliasing implementation should no longer be used. The public aliasing
 *              is now handled by the request listener.
 */
class ProviderDecorator extends DelegatingProvider
{
    /**
     * Constructor
     *
     * @param Aliasing $aliasing
     */
    public function __construct(Aliasing $aliasing)
    {
        parent::__construct();

        $this->aliasing = $aliasing;
    }


    /**
     * @{inheritDoc}
     */
    public function url($object, array $options = array())
    {
        try {
            $ret = parent::url($object, $options);
        } catch (UnsupportedException $e) {
            if (is_string($object)) {
                // allows for referencing strings to be aliased separately.
                $ret = $object;
            } else {
                throw $e;
            }
        }
        if ((!isset($options['aliasing']) || $options['aliasing'] == false)
            && $publicUrl = $this->aliasing->hasPublicAlias($ret)
        ) {
            $ret = $publicUrl;
        }
        return $ret;
    }
    
    /**
     * @{inheritDoc}
     */
    public function all(SecurityContextInterface $security)
    {
        $urlList = parent::all($security);

        foreach ($urlList as &$info) {
            $info['value'] = $this->url($info['value']);
        }

        return $urlList;
    }
}
