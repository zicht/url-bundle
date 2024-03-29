<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\XmlMapper;
use Zicht\Bundle\UrlBundle\Url\AliasSitemapProvider;
use Zicht\Bundle\UrlBundle\Url\SitemapProvider;

/**
 * Registers all tagged services
 */
class UrlMapperPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('zicht_url.aliasing') === false) {
            return;
        }

        $mappers = $container->findTaggedServiceIds('zicht_url.url_mapper');

        if (sizeof($mappers) === 0) {
            return;
        }

        // Order mapper by their priority
        uasort(
            $mappers,
            function ($a, $b) {
                return (isset($b[0]['priority']) ? $b[0]['priority'] : 0) - (isset($a[0]['priority']) ? $a[0]['priority'] : 0);
            }
        );

        $aliasing = $container->getDefinition('zicht_url.aliasing');

        $sitemapIsAliased = false;

        if ($container->getDefinition($container->getAlias(SitemapProvider::class))->getClass() === AliasSitemapProvider::class) {
            // aliasing is not needed for this implementation
            $sitemapIsAliased = true;
        }

        foreach ($mappers as $serviceId => $info) {
            $contentMapper = $container->getDefinition($serviceId);
            if ($sitemapIsAliased && $contentMapper->getClass() === XmlMapper::class) {
                // no need for aliasing the urls in the sitemapper, because it provides it's own aliasing.
                // this is a performance optimization, since sitemaps can get extremely large.
                continue;
            }
            $aliasing->addMethodCall('addMapper', [$contentMapper]);
        }
    }
}
