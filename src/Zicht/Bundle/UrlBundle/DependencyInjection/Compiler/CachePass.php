<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CachePass implements CompilerPassInterface
{
    /**
     * @{inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('zicht_url.cache')) {
            $container->setAlias('zicht_url.provider', 'zicht_url.cache_wrapper');
        } else {
            $container->setAlias('zicht_url.provider', 'zicht_url.provider.delegator');
        }
    }
}