<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Aliasing;

use Symfony\Component\Security\Core\SecurityContextInterface;
use \Zicht\Bundle\UrlBundle\Exception\UnsupportedException;
use \Zicht\Bundle\UrlBundle\Url\DelegatingProvider;

/**
 * Decorator for translating an url into a public alias.
 */
class ProviderDecorator extends DelegatingProvider
{
}