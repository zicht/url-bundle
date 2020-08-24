<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zicht\Bundle\UrlBundle\Url\Provider as UrlProvider;

/**
 * Replaces the url provider with a cache wrapper, if enabled.
 */
class CachePass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('zicht_url.cache')) {
            $container->setAlias('zicht_url.provider', 'zicht_url.cache_wrapper')->setPublic(true);
            $container->setAlias(UrlProvider::class, 'zicht_url.cache_wrapper')->setPublic(true);

            $container->getDefinition('zicht_url.cache')->addTag('zicht_cache.cache_stats');
        } else {
            $container->setAlias('zicht_url.provider', 'zicht_url.provider.delegator')->setPublic(true);
            $container->setAlias(UrlProvider::class, 'zicht_url.provider.delegator')->setPublic(true);
        }
    }
}
