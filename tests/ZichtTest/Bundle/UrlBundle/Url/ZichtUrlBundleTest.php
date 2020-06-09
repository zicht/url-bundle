<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ZichtUrlBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testCompilerPasses()
    {
        $cb = $this->getMock(ContainerBuilder::class, ['addCompilerPass']);

        $cb->expects($this->at(0))->method('addCompilerPass');
        $cb->expects($this->at(1))->method('addCompilerPass');
        $cb->expects($this->at(2))->method('addCompilerPass');

        $b = new \Zicht\Bundle\UrlBundle\ZichtUrlBundle();
        $b->build($cb);
    }
}