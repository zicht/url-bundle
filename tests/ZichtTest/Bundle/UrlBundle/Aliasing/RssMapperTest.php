<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use Symfony\Component\HttpFoundation\Request;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\HtmlMapper;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\RssMapper;

class RssMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider aliasingTestCases
     * @param $input
     * @param $expectedOutput
     * @param $aliasingMap
     */
    public function testInternalToPublicAliasing($input, $expectedOutput, $aliasingMap)
    {
        $aliaser = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Aliasing\Aliasing')->disableOriginalConstructor()->setMethods(array('getAliasingMap'))->getMock();
        $mapper = new RssMapper();

        $aliaser->expects($this->once())->method('getAliasingMap')->will($this->returnValue($aliasingMap));
        $this->assertEquals($expectedOutput, $mapper->processAliasing($input, 'internal-to-public', $aliaser, ['zicht.nl']));
    }

    public function aliasingTestCases()
    {
        return [
            ['<link>http://zicht.nl/foo</link>', '<link>http://zicht.nl/bar</link>', ['http://zicht.nl/foo' => 'http://zicht.nl/bar']],
            ['<link>http://example.org/x</link>', '<link>http://example.org/x</link>', ['http://zicht.nl/foo' => 'http://zicht.nl/bar']],
        ];
    }


    public function testSupports()
    {
        $this->assertTrue((new RssMapper())->supports('application/rss+xml'));
        $this->assertFalse((new RssMapper())->supports('text/xml'));
    }
}
