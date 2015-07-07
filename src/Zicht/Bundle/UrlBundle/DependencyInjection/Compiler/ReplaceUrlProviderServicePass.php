<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\DependencyInjection\Compiler;

use \Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @deprecated Should no longer be used. Remains here for BC.
 * @see ZichtUrlExtension::load
 */
class ReplaceUrlProviderServicePass implements CompilerPassInterface
{
    /**
     * @{inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        // TODO This is here for BC, to be removed whenever the BC for the ProviderDecorator for aliasing is removed.

        $container->setAlias('zicht_url.provider.real', 'zicht_url.provider.delegator');
    }
}
