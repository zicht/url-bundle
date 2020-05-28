<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Listener;

/**
 * Configuration schema for the url bundle
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zicht_url');
        $rootNode = $treeBuilder->getRootNode();

        $isBool = function ($v) {
            return is_bool($v);
        };
        $convertToEnabledKey = function ($v) {
            return ['enabled' => $v];
        };

        $rootNode
            ->children()
                ->booleanNode('strict_public_url')->defaultValue(true)->end()
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
                        ->enumNode('slash_suffix_handling')
                            ->values([
                                Listener::SLASH_SUFFIX_IGNORE,
                                Listener::SLASH_SUFFIX_ACCEPT,
                                Listener::SLASH_SUFFIX_REDIRECT_PERM,
                                Listener::SLASH_SUFFIX_REDIRECT_TEMP,
                            ])
                            ->defaultValue(Listener::SLASH_SUFFIX_IGNORE)
                        ->end()
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
                            'link' => ['href'],
                        ]
                    )
                ->end()
                // usage if this setting is deprecated and no longer has any effect.
                ->variableNode('unalias_subscriber')->end()
            ->end();

        return $treeBuilder;
    }
}
