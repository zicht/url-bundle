<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ZichtUrlBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DependencyInjection\Compiler\UrlProviderPass());
        $container->addCompilerPass(new DependencyInjection\Compiler\ReplaceUrlProviderServicePass());
    }
}