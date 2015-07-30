<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;


use Zicht\Bundle\UrlBundle\Aliasing\HtmlMapper;

class HtmlMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider aliasingTestCases
     * @param $input
     * @param $expectedOutput
     * @param $aliasingMap
     */
    public function testInternalToPublicAliasing($input, $expectedOutput, $aliasingMap, $expectsProcessingToBeExecuted = true)
    {
        $aliaser = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Aliasing\Aliasing')->disableOriginalConstructor()->setMethods(array('getAliasingMap'))->getMock();

        if ($expectsProcessingToBeExecuted) {
            $aliaser->expects($this->once())->method('getAliasingMap')->will($this->returnValue($aliasingMap));
            $this->assertEquals($expectedOutput, HtmlMapper::processAliasingInHtml($input, 'internal-to-public', $aliaser));
        } else {
            $aliaser->expects($this->never())->method('getAliasingMap');
            HtmlMapper::processAliasingInHtml($input, 'internal-to-public', $aliaser);
        }
    }

    public function aliasingTestCases()
    {
        return [
            ['<a href="/foo">', '<a href="/bar">', ['/foo' => '/bar']],
            ['<img src="/foo">', '<img src="/bar">', ['/foo' => '/bar']],
            ['<form action="/foo">', '<form action="/bar">', ['/foo' => '/bar']],
            ['<form action="/foo"><img alt="/foo">', '<form action="/bar"><img alt="/foo">', ['/foo' => '/bar']],

            // test cases that should not do any processing get the last 'false' parameter:
            ['<img alt="/foo">', '<img alt="/foo">', ['/foo' => '/bar'], false],
        ];
    }
}