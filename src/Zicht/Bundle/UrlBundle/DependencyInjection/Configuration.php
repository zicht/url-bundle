<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration schema for the url bundle
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

        $isBool = function ($v) {
            return is_bool($v);
        };
        $convertToEnabledKey = function ($v) {
            return ['enabled' => $v];
        };

        $rootNode
            ->children()
                ->booleanNode('strict_public_url')->defaultValue(true)->end()
                ->booleanNode('multi_site')->defaultValue(false)->end()
                ->arrayNode('static_ref')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('aliasing')
                    ->beforeNormalization()
                        ->ifTrue($isBool)
                        ->then($convertToEnabledKey)
                    ->end()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultValue(false)->end()
                        ->booleanNode('enable_params')->defaultValue(false)->end()
                        ->arrayNode('exclude_patterns')->prototype('scalar')->end()->end()
                        ->arrayNode('automatic_entities')->prototype('scalar')->end()->end()
                    ->end()
                ->end()
                ->variableNode('logging')->end()
                ->booleanNode('admin')->defaultValue(false)->end()
                ->arrayNode('db_static_ref')
                    ->beforeNormalization()
                        ->ifTrue($isBool)
                        ->then($convertToEnabledKey)
                    ->end()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultValue(false)->end()
                        ->scalarNode('fallback_locale')->defaultValue(null)->end()
                    ->end()
                ->end()
                ->arrayNode('caching')
                    ->beforeNormalization()
                        ->ifTrue($isBool)
                        ->then($convertToEnabledKey)
                    ->end()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultValue(false)->end()
                        ->arrayNode('entities')->prototype('scalar')->end()->end()
                    ->end()
                ->end()
                ->variableNode('html_attributes')
                    ->treatNullLike([])
                    ->defaultValue(
                        [
                            'a' => ['href', 'data-href'],
                            'area' => ['href', 'data-href'],
                            'iframe' => ['src'],
                            'form' => ['action'],
                            'meta' => ['content'],
                            'link' => ['href']
                        ]
                    )
                ->end()
                // usage if this setting is deprecated and no longer has any effect.
                ->variableNode('unalias_subscriber')->end()
            ->end();

        return $treeBuilder;
    }
}
