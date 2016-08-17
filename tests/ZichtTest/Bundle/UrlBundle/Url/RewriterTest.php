<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url;

use Zicht\Bundle\UrlBundle\Url\Rewriter;

class RewriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider extractPathTestCases
     */
    public function testExtractPath($expected, $input)
    {
        $rewriter = new Rewriter();
        $this->assertEquals(
            $expected,
            $rewriter->extractPath($input)
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
        $rewriter = new Rewriter();
        $this->assertEquals(
            $expected,
            $rewriter->rewrite(
                $input,
                $mappings,
                $localDomains
            )
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