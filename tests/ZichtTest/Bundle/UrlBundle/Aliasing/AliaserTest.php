<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use \Zicht\Bundle\UrlBundle\Aliasing\ProviderDecorator;


class AliaserTest extends \PHPUnit_Framework_TEstCAse
{
    public function setUp()
    {
        $this->aliasing = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Aliasing\Aliasing')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Url\StaticProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('url'))
            ->getMock()
        ;

        $this->aliaser = new \Zicht\Bundle\UrlBundle\Aliasing\Aliaser($this->aliasing, $this->provider);
    }


    public function testAliasing()
    {

        $foo = 'wut';
        $this->provider->expects($this->once())->method('url')->with($foo)->will($this->returnValue('/baz/bat'));
        $this->aliasing->expects($this->once())->method('hasPublicAlias')->with('/baz/bat')->will($this->returnValue(false));
        $this->aliasing->expects($this->once())->method('addAlias')->with(
            '/wut',
            '/baz/bat',
            \Zicht\Bundle\UrlBundle\Entity\UrlAlias::REWRITE,
            \Zicht\Bundle\UrlBundle\Aliasing\Aliasing::STRATEGY_SUFFIX
        )->will($this->returnValue(true));
        $this->assertTrue($this->aliaser->createAlias($foo));
    }


    public function testExistingWillNotCreateAlias()
    {

        $foo = 'wut';
        $this->provider->expects($this->once())->method('url')->with($foo)->will($this->returnValue('/baz/bat'));
        $this->aliasing->expects($this->once())->method('hasPublicAlias')->with('/baz/bat')->will($this->returnValue(true));
        $this->aliasing->expects($this->never())->method('addAlias');
        $this->assertFalse($this->aliaser->createAlias($foo));
    }
}