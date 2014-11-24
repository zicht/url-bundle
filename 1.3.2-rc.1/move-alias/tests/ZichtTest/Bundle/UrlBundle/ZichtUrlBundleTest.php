<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\UrlBundle\Url;

class ZichtUrlBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testCompilerPasses()
    {
        $cb = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $cb->expects($this->at(0))->method('addCompilerPass');
        $cb->expects($this->at(1))->method('addCompilerPass');
        $cb->expects($this->at(2))->method('addCompilerPass');

        $b = new \Zicht\Bundle\UrlBundle\ZichtUrlBundle();
        $b->build($cb);
    }
}