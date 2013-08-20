<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
 
class UrlProviderPass implements CompilerPassInterface
{
    /**
     * @{inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('zicht_url.provider.delegator');
        foreach ($container->findTaggedServiceIds('zicht_url.url_provider') as $id => $attributes) {
            $priority = 0;
            if (isset($attributes[0]['priority'])) {
                $priority = (int)$attributes[0]['priority'];
            }
            $definition->addMethodCall('addProvider', array(new Reference($id), $priority));
        }
    }
}