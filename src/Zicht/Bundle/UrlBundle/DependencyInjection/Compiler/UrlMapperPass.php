<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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

        foreach ($mappers as $serviceId => $info) {
            $contentMapper = $container->getDefinition($serviceId);
            $aliasing->addMethodCall('addMapper', array($contentMapper));
        }
    }
}
