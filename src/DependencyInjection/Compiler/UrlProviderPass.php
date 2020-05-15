<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers all tagged services
 */
class UrlProviderPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('zicht_url.provider.delegator')) {
            $definition = $container->getDefinition('zicht_url.provider.delegator');
            foreach ($container->findTaggedServiceIds('zicht_url.url_provider') as $id => $attributes) {
                $priority = 0;
                if (isset($attributes[0]['priority'])) {
                    $priority = (int)$attributes[0]['priority'];
                }
                $definition->addMethodCall('addProvider', [new Reference($id), $priority]);
            }
        }
    }
}
