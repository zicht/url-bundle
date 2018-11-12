<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Url\Params\Translator;
use Zicht\Bundle\UrlBundle\Url\Params\UriParser;

class UriParserTest extends TestCase
{
    function testParser()
    {
        $parser = new UriParser();
        $this->assertEquals(
            [
                'a' => ['x'],
                'b' => ['y', 'z'],
            ],
            $parser->parseUri('a=x/b=y,z')
        );
    }


    function testParserWithTranslator()
    {
        $parser = new UriParser();
        $parser->setTranslator(
            new Translator\StaticTranslator(
                'a',
                'A',
                ['x' => 'X', 'y' => 'Y', 'z' => 'Z']
            )
        );
        $this->assertEquals(
            [
                'a' => ['x', 'y', 'z']
            ],
            $parser->parseUri('A=X,Y,Z')
        );
    }


    function testParserWithCustomSeparators()
    {
        $parser = new UriParser(';', '|', '^');
        $this->assertEquals(
            [
                'a' => ['x'],
                'b' => ['y', 'z'],
            ],
            $parser->parseUri('a|x;b|y^z')
        );
    }


    function testComposer()
    {
        $parser = new UriParser();
        $this->assertEquals(
            'a=x/b=y,z',
            $parser->composeUri(
                [
                    'a' => ['x'],
                    'b' => ['y', 'z'],
                ]
            )
        );
    }


    function testComposerWithTranslator()
    {
        $parser = new UriParser();
        $parser->setTranslator(
            new Translator\StaticTranslator(
                'a',
                'A',
                ['x' => 'X', 'y' => 'Y', 'z' => 'Z']
            )
        );
        $this->assertEquals(
            'A=X,Y,Z',
            $parser->composeUri(['a' => ['x', 'y', 'z']])
        );
    }


    function testComposerWithCustomSeparators()
    {
        $parser = new UriParser(';', '|', '^');
        $this->assertEquals(
            'a|x;b|y^z',
            $parser->composeUri(
                [
                    'a' => ['x'],
                    'b' => ['y', 'z'],
                ]
            )
        );
    }
}
