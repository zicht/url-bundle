<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\XmlMapper;
use Zicht\Bundle\UrlBundle\Url\AliasSitemapProvider;

/**
 * Registers all tagged services
 */
class UrlMapperPass implements CompilerPassInterface
{
    /**
     * @{inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('zicht_url.aliasing') === false) {
            return;
        }

        $mappers = $container->findTaggedServiceIds('zicht_url.url_mapper');

        if (sizeof($mappers) === 0) {
            return;
        }

        $aliasing = $container->getDefinition('zicht_url.aliasing');

        $sitemapIsAliased = false;

        if ($container->getDefinition($container->getAlias('zicht_url.sitemap_provider'))->getClass() === AliasSitemapProvider::class) {
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
            $aliasing->addMethodCall('addMapper', array($contentMapper));
        }
    }
}
