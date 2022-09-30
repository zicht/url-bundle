<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Twig;

use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Url\ShortUrlManager;

/**
 * @property \Zicht\Bundle\UrlBundle\Url\Provider $provider
 * @property \Zicht\Bundle\UrlBundle\Twig\UrlExtension $extension
 */
class ExtensionTest extends TestCase
{
    public function setUp(): void
    {
        $this->provider = (new Generator())->getMock('Zicht\Bundle\UrlBundle\Url\Provider', ['url', 'supports']);
        $shortUrlManager = self::getMockBuilder(ShortUrlManager::class)->disableOriginalConstructor()->getMock();
        $this->extension = new \Zicht\Bundle\UrlBundle\Twig\UrlExtension($this->provider, $shortUrlManager);
    }

    public function testAvailableFunctions()
    {
        $functions = $this->extension->getFunctions();

        $this->assertArrayHasKey('object_url', $functions);
        $this->assertArrayHasKey('static_reference', $functions);
        $this->assertArrayHasKey('static_ref', $functions);
    }

    public function testObjectUrl()
    {
        $this->provider->expects($this->once())->method('url')->with('foo')->will($this->returnValue('bar'));

        $this->assertEquals('bar', $this->extension->objectUrl('foo'));
    }

    public function testObjectUrlWillDefaultIfADefaultIsPassed()
    {
        $this->provider->expects($this->once())->method('url')->with('foo')
            ->will($this->throwException(new \Zicht\Bundle\UrlBundle\Exception\UnsupportedException()));
        $this->assertEquals('qux', $this->extension->objectUrl('foo', 'qux'));
    }

    public function testObjectUrlWillRethrowIfNotSupportedAndNoDefaultGiven()
    {
        $this->expectException('\Zicht\Bundle\UrlBundle\Exception\UnsupportedException');
        $this->provider->expects($this->once())->method('url')->with('foo')
            ->will($this->throwException(new \Zicht\Bundle\UrlBundle\Exception\UnsupportedException()));
        $this->extension->objectUrl('foo');
    }

    public function testObjectUrlWillDEfaultToToStringIfDefaultIsTrue()
    {
        $this->provider->expects($this->once())->method('url')->with('foo')
            ->will($this->throwException(new \Zicht\Bundle\UrlBundle\Exception\UnsupportedException()));

        $this->assertEquals('foo', $this->extension->objectUrl('foo', true));
    }

    public function testStaticRef()
    {
        $this->provider->expects($this->once())->method('url')->with('foo')->will($this->returnValue('bar'));

        $this->assertEquals('bar', $this->extension->staticRef('foo'));
        $this->assertEquals('bar', $this->extension->staticRef('foo'));
    }

    public function testStaticRefParams()
    {
        $this->provider->expects($this->once())->method('url')->with('foo')->will($this->returnValue('bar'));

        $this->assertEquals('bar?a=b', $this->extension->staticRef('foo', ['a' => 'b']));
        $this->assertEquals('bar?a=b', $this->extension->staticRef('foo', ['a' => 'b']));
    }

    public function testStaticRefDefaultsToString()
    {
        $this->provider->expects($this->once())->method('url')->with('foo')
            ->will($this->throwException(new \Zicht\Bundle\UrlBundle\Exception\UnsupportedException()));

        $this->assertTrue(is_string($this->extension->staticRef('foo')));
    }
}
