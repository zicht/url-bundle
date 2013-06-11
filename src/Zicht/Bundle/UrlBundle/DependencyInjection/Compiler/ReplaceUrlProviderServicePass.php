<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\DependencyInjection\Compiler;

use \Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\DependencyInjection\Definition;
use \Symfony\Component\DependencyInjection\Reference;

/**
 * Replaces the URL provider with the aliasing version, if aliasing was enabled for the ZichtUrlBundle.
 *
 * @see ZichtUrlExtension::load
 */
class ReplaceUrlProviderServicePass implements \Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    /**
     * @{inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('zicht_url.aliasing')) {
            $def = $container->getDefinition('zicht_url.provider.delegator');
            $container->setDefinition('zicht_url.provider.real', $def);
            $def = new Definition('Zicht\Bundle\UrlBundle\Aliasing\ProviderDecorator');
            $def->addArgument(new Reference('zicht_url.aliasing'));
            $def->addMethodCall('addProvider', array(new Reference('zicht_url.provider.real')));
            $container->setDefinition('zicht_url.provider.delegator', $def);
        }
    }

}
