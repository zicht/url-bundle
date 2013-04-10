<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zicht_url');

        $rootNode
            ->children()
                ->arrayNode('static_ref')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->variableNode('aliasing')->end()
                ->variableNode('logging')->end()
                ->variableNode('admin')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
