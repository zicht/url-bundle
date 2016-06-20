<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use Symfony\Component\HttpFoundation\Request;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\HtmlMapper;

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
        $mapper = new HtmlMapper();

        if ($expectsProcessingToBeExecuted) {
            $aliaser->expects($this->once())->method('getAliasingMap')->will($this->returnValue($aliasingMap));
            $this->assertEquals($expectedOutput, $mapper->processAliasing($input, 'internal-to-public', $aliaser, ['zicht.nl']));
        } else {
            $aliaser->expects($this->never())->method('getAliasingMap');
            $mapper->processAliasing($input, 'internal-to-public', $aliaser, 'zicht.nl');
        }
    }

    public function aliasingTestCases()
    {
        return [
            ['<meta property="og:url" content="http://zicht.nl/foo">', '<meta property="og:url" content="http://zicht.nl/bar">', ['/foo' => '/bar']],
            ['<meta property="og:url" content="https://zicht.nl/foo">', '<meta property="og:url" content="https://zicht.nl/bar">', ['/foo' => '/bar']],
            ['<a href="/foo">', '<a href="/bar">', ['/foo' => '/bar']],
            ['<a href="/foo?param=value">', '<a href="/bar?param=value">', ['/foo' => '/bar']],
            ['<img src="/foo">', '<img src="/bar">', ['/foo' => '/bar']],
            ['<form action="/foo">', '<form action="/bar">', ['/foo' => '/bar']],

            ['<form action="/foo"><img alt="/foo">', '<form action="/bar"><img alt="/foo">', ['/foo' => '/bar']],
            ['<form action="/foo?key=value"><img alt="/foo">', '<form action="/bar?key=value"><img alt="/foo">', ['/foo' => '/bar']],

            ['<a href="/a/b/c/x=1/y=1">', '<a href="/x/y/z/x=1/y=1">', ['/a/b/c' => '/x/y/z']],
            ['<a href="/a/b/c/x=some%20space/y=1">', '<a href="/x/y/z/x=some%20space/y=1">', ['/a/b/c' => '/x/y/z']],

            // test cases that should not do any processing are identified by the last 'false' parameter:
            ['<img alt="/foo">', '<img alt="/foo">', ['/foo' => '/bar'], false],

        ];
    }
}
