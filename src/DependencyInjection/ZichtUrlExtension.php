<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Zicht\Bundle\UrlBundle\Url\Params\TranslatedUriParser;
use Zicht\Bundle\UrlBundle\Url\Params\UriParser;
use Zicht\Bundle\UrlBundle\Url\ShortUrlManager;

/**
 * DI Extension for the URL services.
 */
class ZichtUrlExtension extends Extension
{
    /**
     * Responds to the twig configuration parameter.
     *
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        if (!empty($config['unalias_subscriber'])) {
            // deprecation, remove in next major
            trigger_error('unalias_subscriber is no longer used. This has moved to form transformers.', E_USER_DEPRECATED);
        }

        if (isset($config['static_ref'])) {
            $container->getDefinition('zicht_url.static_refs')->addMethodCall('addAll', [$config['static_ref']]);
        }
        if (!empty($config['aliasing']) && $config['aliasing']['enabled'] === true) {
            $this->loadAliasingConfig($container, $config['aliasing'], $loader);
            $container->setAlias('zicht_url.sitemap_provider', new Alias('zicht_url.alias_sitemap_provider'))->setPublic(true);
        } else {
            $container->setAlias('zicht_url.sitemap_provider', new Alias('zicht_url.provider'))->setPublic(true);
        }
        if (!empty($config['logging'])) {
            $loader->load('logging.xml');
        }
        if (!empty($config['admin'])) {
            $loader->load('admin.xml');
        }
        if (!empty($aliasingConfig) && $config['caching']['enabled'] === true) {
            $loader->load('cache.xml');
            $subscriberDefinition = $container->getDefinition('zicht_url.cache_subscriber');
            $subscriberDefinition->replaceArgument(1, $config['caching']['entities']);
        }
        if (!empty($config['db_static_ref']) && $config['db_static_ref']['enabled'] === true) {
            $loader->load('db.xml');
        }

        if (!empty($config['url_params'])) {
            if (isset($config['url_params']['param_separator'])) {
                $container->getDefinition(UriParser::class)->setArgument('$paramSeparator', (string)$config['url_params']['param_separator']);
                $container->getDefinition(TranslatedUriParser::class)->setArgument('$paramSeparator', (string)$config['url_params']['param_separator']);
            }
            if (isset($config['url_params']['key_value_separator'])) {
                $container->getDefinition(UriParser::class)->setArgument('$keyValueSeparator', (string)$config['url_params']['key_value_separator']);
                $container->getDefinition(TranslatedUriParser::class)->setArgument('$keyValueSeparator', (string)$config['url_params']['key_value_separator']);
            }
            if (isset($config['url_params']['value_separator'])) {
                $container->getDefinition(UriParser::class)->setArgument('$valueSeparator', (string)$config['url_params']['value_separator']);
                $container->getDefinition(TranslatedUriParser::class)->setArgument('$valueSeparator', (string)$config['url_params']['value_separator']);
            }
        }

        if ($container->hasDefinition('zicht_url.aliasing')) {
            $container->getDefinition('zicht_url.twig_extension')->addArgument(new Reference('zicht_url.aliasing'));
        }

        if ($container->hasDefinition(ShortUrlManager::class)) {
            $container->getDefinition(ShortUrlManager::class)->addArgument(new Reference('zicht_url.aliasing'));
        }

        if ($container->hasDefinition('zicht_url.mapper.html')) {
            $container->getDefinition('zicht_url.mapper.html')->addMethodCall('addAttributes', [$config['html_attributes']]);
        }

        if ($container->hasDefinition('zicht_url.listener.strict_public_url')) {
            $container->getDefinition('zicht_url.listener.strict_public_url')->replaceArgument(0, $config['strict_public_url']);
        }

        if ($container->hasDefinition('zicht_url.validator.contains_url_alias')) {
            $container->getDefinition('zicht_url.validator.contains_url_alias')->replaceArgument(1, $config['strict_public_url']);
        }

        $formResources = $container->getParameter('twig.form.resources');
        $formResources[] = '@ZichtUrl/form_theme.html.twig';
        $container->setParameter('twig.form.resources', $formResources);
    }

    /**
     * @param array $aliasingConfig
     * @param XmlFileLoader $loader
     * @return void
     */
    protected function loadAliasingConfig(ContainerBuilder $container, $aliasingConfig, $loader)
    {
        $loader->load('aliasing.xml');

        $listenerDefinition = $container->getDefinition('zicht_url.aliasing_listener');
        if ($aliasingConfig['exclude_patterns']) {
            $listenerDefinition->addMethodCall('setExcludePatterns', [$aliasingConfig['exclude_patterns']]);
        }

        $listenerDefinition->addMethodCall('setIsParamsEnabled', [$aliasingConfig['enable_params']]);

        if ($aliasingConfig['automatic_entities']) {
            $automaticAliasDoctrineDefinition = $container->getDefinition('zicht_url.aliasing.doctrine.subscriber');

            foreach ($aliasingConfig['automatic_entities'] as $entityClass) {
                $automaticAliasDoctrineDefinition->addMethodCall('addEntityClass', [$entityClass]);
            }
        }
    }
}
