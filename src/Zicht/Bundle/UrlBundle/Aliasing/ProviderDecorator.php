<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
 
namespace Zicht\Bundle\UrlBundle\Aliasing;

use Zicht\Bundle\UrlBundle\Exception\UnsupportedException;
use \Zicht\Bundle\UrlBundle\Url\DelegatingProvider;

/**
 * Decorator for translating an url into a public alias.
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
}