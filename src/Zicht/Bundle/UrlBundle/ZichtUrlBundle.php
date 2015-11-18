<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle instance for zicht/url-bundle
 */
class ZichtUrlBundle extends Bundle
{
    /**
     * @{inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DependencyInjection\Compiler\UrlProviderPass());
        $container->addCompilerPass(new DependencyInjection\Compiler\ReplaceUrlProviderServicePass());
        $container->addCompilerPass(new DependencyInjection\Compiler\CachePass());
        $container->addCompilerPass(new DependencyInjection\Compiler\UrlMapperPass());
    }
}