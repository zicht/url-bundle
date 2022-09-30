<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Originally replaced the regular provider with a decorating one.
 *
 * @see ZichtUrlExtension::load
 * @deprecated Should no longer be used. Remains here for BC.
 */
class ReplaceUrlProviderServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // TODO This is here for BC, to be removed whenever the BC for the ProviderDecorator for aliasing is removed.
        $container->setAlias('zicht_url.provider.real', 'zicht_url.provider.delegator');
    }
}
