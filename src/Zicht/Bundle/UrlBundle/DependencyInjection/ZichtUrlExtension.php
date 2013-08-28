<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection;

use \Symfony\Component\HttpKernel\DependencyInjection\Extension;
use \Symfony\Component\DependencyInjection\ContainerBuilder;
use \Symfony\Component\Config\FileLocator;
use \Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use \Symfony\Component\DependencyInjection\Definition;
use \Symfony\Component\DependencyInjection\Reference;

/**
 * DI Extension for the URL services.
 */
class ZichtUrlExtension extends Extension
{
    /**
     * Responds to the twig configuration parameter.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        if (isset($config['static_ref'])) {
            $container->getDefinition('zicht_url.static_refs')->addMethodCall('addAll', array($config['static_ref']));
        }
        if (!empty($config['aliasing']) && $config['aliasing']['enabled'] === true) {
            $loader->load('aliasing.xml');

            $listenerDefinition = $container->getDefinition('zicht_url.aliasing_listener');
            if ($config['aliasing']['exclude_patterns']) {
                $listenerDefinition->addMethodCall('setExcludePatterns', array($config['aliasing']['exclude_patterns']));
            }
            $listenerDefinition->addMethodCall('setIsParamsEnabled', array($config['aliasing']['enable_params']));
        }
        if (!empty($config['logging'])) {
            $loader->load('logging.xml');
        }
        if (!empty($config['admin'])) {
            $loader->load('admin.xml');
        }
        if (!empty($config['aliasing']) && $config['caching']['enabled'] === true) {
            $loader->load('cache.xml');
            $subscriberDefinition = $container->getDefinition('zicht_url.cache_subscriber');
            $subscriberDefinition->replaceArgument(1, $config['caching']['entities']);
        }
        if (!empty($config['db_static_ref']) && $config['db_static_ref']['enabled'] === true) {
            $loader->load('db.xml');
        }
    }
}
