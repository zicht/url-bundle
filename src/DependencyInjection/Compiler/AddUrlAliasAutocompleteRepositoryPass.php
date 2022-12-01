<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * Zicht Admin Quicklist service should have a 'url_alias' "repository". Either configure one in
 * `config/packages/zicht_admin.yaml` or this Compiler Pass will add one.
 */
class AddUrlAliasAutocompleteRepositoryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('zicht_admin.quicklist')) {
            /** @var @see \Zicht\Bundle\AdminBundle\Service\Quicklist::addRepositoryConfig() */
            $definition = $container->getDefinition('zicht_admin.quicklist');
            if (0 === count(array_filter($definition->getMethodCalls(), static fn (array $call) => isset($call[0], $call[1][0]) && $call[0] === 'addRepositoryConfig' && $call[1][0] === 'url_alias'))) {
                $definition->addMethodCall('addRepositoryConfig', ['url_alias', [
                    'repository' => UrlAlias::class,
                    'fields' => ['public_url'],
                    'exposed' => false,
                ]]);
            }
        }
    }
}
