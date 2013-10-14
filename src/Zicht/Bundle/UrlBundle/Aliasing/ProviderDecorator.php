<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
 
namespace Zicht\Bundle\UrlBundle\Aliasing;

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
        $ret = parent::url($object, $options);
        if ((!isset($options['aliasing']) || $options['aliasing'] == false)
            && $publicUrl = $this->aliasing->hasPublicAlias($ret)
        ) {
            $ret = $publicUrl;
        }
        return $ret;
    }
}