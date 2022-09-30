<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\HtmlMapper;
use Zicht\Bundle\UrlBundle\Url\Rewriter;

class HtmlMapperTest extends TestCase
{
    /**
     * @dataProvider aliasingTestCases
     * @param $input
     * @param $expectedOutput
     * @param $aliasingMap
     */
    public function testInternalToPublicAliasing($input, $expectedOutput, $aliasingMap)
    {
        $aliaser = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Aliasing\Aliasing')->disableOriginalConstructor()->setMethods(['getAliasingMap'])->getMock();
        $mapper = new HtmlMapper();
        $aliaser->expects($this->once())->method('getAliasingMap')->will($this->returnValue($aliasingMap));
        $this->assertEquals($expectedOutput, $mapper->processAliasing($input, 'internal-to-public', new Rewriter($aliaser), ['zicht.nl']));
    }

    /**
     * Test if addAttributes adds new/non-standard attributes are processed properly.
     *
     * @param string $input
     * @param string $expectedOutput
     * @param array $aliasingMap
     *
     * @dataProvider alternativeTagsAliasingTestCases
     */
    public function testAddAttributesAllowsExtraAttributesToBeProcessed($input, $expectedOutput, $aliasingMap)
    {
        $aliaser = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Aliasing\Aliasing')->disableOriginalConstructor()->setMethods(['getAliasingMap'])->getMock();
        $mapper = new HtmlMapper();
        $mapper->addAttributes(
            [
                'option' => ['value'],
                'select' => ['data-href'],
            ]
        );
        $aliaser->expects($this->once())->method('getAliasingMap')->will($this->returnValue($aliasingMap));
        $this->assertEquals($expectedOutput, $mapper->processAliasing($input, 'internal-to-public', new Rewriter($aliaser), ['zicht.nl']));
    }

    public function aliasingTestCases()
    {
        return [
            ['<meta property="og:url" content="http://zicht.nl/foo">', '<meta property="og:url" content="http://zicht.nl/bar">', ['http://zicht.nl/foo' => 'http://zicht.nl/bar']],
            ['<meta property="og:url" content="https://zicht.nl/foo">', '<meta property="og:url" content="https://zicht.nl/bar">', ['https://zicht.nl/foo' => 'https://zicht.nl/bar']],
            ['<link rel="canonical" href="https://zicht.nl/foo"><meta property="og:url" content="https://zicht.nl/foo">', '<link rel="canonical" href="https://zicht.nl/bar"><meta property="og:url" content="https://zicht.nl/bar">', ['https://zicht.nl/foo' => 'https://zicht.nl/bar']],
            ['<link rel="canonical" href="https://zicht.nl/foo">', '<link rel="canonical" href="https://zicht.nl/bar">', ['https://zicht.nl/foo' => 'https://zicht.nl/bar']],
            ['<a href="/foo?param=value">', '<a href="/bar?param=value">', ['/foo?param=value' => '/bar?param=value']],
            ['<a href="/foo?param=value&k=v">', '<a href="/foo?param=value&k=v">', ['/foo?param=value' => '/bar?param=value']],
            ['<img alt="/foo">', '<img alt="/foo">', ['/foo' => '/bar']],
        ];
    }

    /**
     * Some non-standard html tags / attributes.
     *
     * @return array
     */
    public function alternativeTagsAliasingTestCases()
    {
        return [
            ['<option value="http://zicht.nl/foo">', '<option value="http://zicht.nl/bar">', ['http://zicht.nl/foo' => 'http://zicht.nl/bar']],
            ['<select data-href="http://zicht.nl/foo">', '<select data-href="http://zicht.nl/bar">', ['http://zicht.nl/foo' => 'http://zicht.nl/bar']],
        ];
    }

    public function testSupports()
    {
        $this->assertTrue((new HtmlMapper())->supports('text/html'));
        $this->assertFalse((new HtmlMapper())->supports('text/xml'));
    }
}
