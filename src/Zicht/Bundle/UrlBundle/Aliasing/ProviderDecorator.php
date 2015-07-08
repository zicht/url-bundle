<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Aliasing;

use \Zicht\Bundle\UrlBundle\Url\DelegatingProvider;

/**
 * Decorator for aliasing. No longer used, may be removed in next major release.
 *
 * @deprecated The provider decorator and it's aliasing implementation should no longer be used. The public aliasing
 *              is now handled by the request listener.
 */
class ProviderDecorator extends DelegatingProvider
{
}