<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\XmlMapper;
use Zicht\Bundle\UrlBundle\Url\Rewriter;

class XmlMapperTest extends TestCase
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
        $mapper = new XmlMapper();

        $aliaser->expects($this->once())->method('getAliasingMap')->will($this->returnValue($aliasingMap));
        $this->assertEquals($expectedOutput, $mapper->processAliasing($input, 'internal-to-public', new Rewriter($aliaser), ['zicht.nl']));
    }

    public function aliasingTestCases()
    {
        return [
            ['<loc>http://zicht.nl/foo</loc>', '<loc>http://zicht.nl/bar</loc>', ['http://zicht.nl/foo' => 'http://zicht.nl/bar']],
            ['<loc>http://example.org/x</loc>', '<loc>http://example.org/x</loc>', ['http://zicht.nl/foo' => 'http://zicht.nl/bar']],
        ];
    }


    public function testSupports()
    {
        $this->assertTrue((new XmlMapper())->supports('text/xml'));
        $this->assertTrue((new XmlMapper())->supports('application/xml'));
        $this->assertFalse((new XmlMapper())->supports('application/xml+xhtml'));
    }
}
