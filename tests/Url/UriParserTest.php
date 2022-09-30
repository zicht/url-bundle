<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Url\Params\Translator;
use Zicht\Bundle\UrlBundle\Url\Params\UriParser;

class UriParserTest extends TestCase
{
    public function testParser()
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

    public function testParserWithTranslator()
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
                'a' => ['x', 'y', 'z'],
            ],
            $parser->parseUri('A=X,Y,Z')
        );
    }

    public function testParserWithCustomSeparators()
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

    public function testComposer()
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

    public function testComposerWithTranslator()
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

    public function testComposerWithCustomSeparators()
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
