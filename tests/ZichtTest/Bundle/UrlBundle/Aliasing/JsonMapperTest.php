<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use Symfony\Component\HttpFoundation\Request;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\HtmlMapper;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\JsonMapper;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\RssMapper;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\XmlMapper;

class JsonMapperTest extends \PHPUnit_Framework_TestCase
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
        $mapper = new JsonMapper();

        $aliaser->expects($this->once())->method('getAliasingMap')->will($this->returnValue($aliasingMap));
        $this->assertEquals($expectedOutput, $mapper->processAliasing($input, 'internal-to-public', $aliaser, ['zicht.nl']));
    }

    public function aliasingTestCases()
    {
        return [
            ['{
                "foo": {
                        "value": "http://zicht.nl/foo"
                }
            }', '{
                "foo": {
                        "value": "http://zicht.nl/bar"
                }
            }', ['http://zicht.nl/foo' => 'http://zicht.nl/bar']]
        ];
    }


    public function testSupports()
    {
        $this->assertTrue((new XmlMapper())->supports('text/xml'));
        $this->assertTrue((new XmlMapper())->supports('application/xml'));
        $this->assertFalse((new XmlMapper())->supports('application/xml+xhtml'));
    }
}
