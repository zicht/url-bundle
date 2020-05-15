<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Url\Rewriter;

class RewriterTest extends TestCase
{
    public function setUp()
    {
        $this->rewriter = new Rewriter($this->getMockBuilder(Aliasing::class)->disableOriginalConstructor()->getMock());
    }

    /**
     * @dataProvider extractPathTestCases
     */
    public function testExtractPath($expected, $input)
    {
        $this->assertEquals(
            $expected,
            $this->rewriter->extractPath($input)
        );
    }

    public function extractPathTestCases()
    {
        return [
            ['/foo', 'https://example.org/foo/x=y?a=b'],
            ['/foo', '//example.org/foo/x=y?a=b'],
            [null, 'mailto:a@b.org'],
//            ['/foo', '//example.org/foo/x=y?a=b']
//            [null, 'https://example.org/foo/x=y?a=b']
        ];
    }

    /**
     * @dataProvider rewriteTestCases
     */
    public function testRewrite($expected, $input, $mappings, $localDomains = [])
    {
        $aliasing = $this->getMockBuilder(Aliasing::class)->disableOriginalConstructor()->getMock();
        $aliasing->expects($this->any())->method('getAliasingMap')->will($this->returnValue($mappings));
        $rewriter = new Rewriter($aliasing);
        $rewriter->setLocalDomains($localDomains);
        $this->assertEquals(
            $expected,
            $rewriter->rewrite($input, 'internal-to-public')
        );
    }

    /**
     *
     */
    public function rewriteTestCases()
    {
        return [
            // simple path rewrite
            [
                [
                    '/foo' => '/bar',
                ],
                ['/foo'],
                ['/foo' => '/bar']
            ],

            // path rewrite, while retaining query string
            [
                [
                    '/foo?a=b' => '/bar?a=b',
                ],
                ['/foo?a=b'],
                ['/foo' => '/bar']
            ],

            // path rewrite, while retaining query string and local domain
            [
                [
                    'http://zicht.nl/foo?a=b' => 'http://zicht.nl/bar?a=b',
                ],
                ['http://zicht.nl/foo?a=b'],
                ['/foo' => '/bar'],
                ['zicht.nl']
            ],

            // path rewrite, while retaining query string, local domain and hash
            [
                [
                    'http://zicht.nl/foo?a=b#somehash' => 'http://zicht.nl/bar?a=b#somehash',
                ],
                ['http://zicht.nl/foo?a=b#somehash'],
                ['/foo' => '/bar'],
                ['zicht.nl']
            ],

            // path rewrite, while retaining slash-separated parameters
            [
                [
                    '/foo/a=b' => '/bar/a=b',
                ],
                ['/foo/a=b'],
                ['/foo' => '/bar']
            ],

            // path rewrite, while retaining slash-separated parameters and local domain
            [
                [
                    'http://zicht.nl/foo/a=b' => 'http://zicht.nl/bar/a=b',
                ],
                ['http://zicht.nl/foo/a=b'],
                ['/foo' => '/bar'],
                ['zicht.nl']
            ],


            // no rewrite if no mapping for path
            [
                [
                    'http://zicht.nl/foo?a=b' => 'http://zicht.nl/foo?a=b',
                ],
                ['http://zicht.nl/foo?a=b'],
                [],
                ['zicht.nl']
            ],

            // rewrite one, don't rewrite others
            [
                [
                    'https://zicht.nl/foo' => 'https://zicht.nl/bar',
                    'https://zicht.nl/foo/a=b' => 'https://zicht.nl/bar/a=b',
                    'https://zicht.nl/unaliased' => 'https://zicht.nl/unaliased',
                    'https://zicht.nl/unaliased?a=b' => 'https://zicht.nl/unaliased?a=b',
                    'https://zicht.nl?a=b' => 'https://zicht.nl?a=b',
                ],
                ['https://zicht.nl/foo', 'https://zicht.nl/foo/a=b', 'https://zicht.nl/unaliased', 'https://zicht.nl/unaliased?a=b', 'https://zicht.nl?a=b'],
                ['/foo' => '/bar'],
                ['zicht.nl']
            ],

            // don't rewrite remote domains
            [
                [
                    'https://zicht.nl/foo' => 'https://zicht.nl/foo',
                    'https://zicht.nl/unaliased' => 'https://zicht.nl/unaliased',
                ],
                ['https://zicht.nl/foo', 'https://zicht.nl/unaliased'],
                ['/foo' => '/bar'],
                ['example.org']
            ]
        ];
    }
}
