<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url;

use PHPUnit\Framework\TestCase;

class StaticProviderTest extends TestCase
{
    public function setUp(): void
    {
        $router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
        $context = new \Symfony\Component\Routing\RequestContext('');
        $router->expects($this->any())->method('getContext')->will($this->returnValue($context));
        $this->provider = new \Zicht\Bundle\UrlBundle\Url\StaticProvider($router, ['a' => 'b']);
    }

    public function testApi()
    {
        $this->assertTrue($this->provider->supports('a'));
        $this->assertEquals('/b', $this->provider->url('a'));
        $this->assertFalse($this->provider->supports('x'));

        $this->provider->addAll(
            [
                'foo' => 'baz'
            ]
        );
        $this->assertTrue($this->provider->supports('foo'));

        $this->provider->add('qux', 'baz');
        $this->assertTrue($this->provider->supports('qux'));

        $this->assertEquals('/baz', $this->provider->url('qux'));
    }
}
