<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\UrlBundle\Url;

use Zicht\Bundle\UrlBundle\Url\Params\UriParser;
use Zicht\Bundle\UrlBundle\Url\Params\Translator;

class UriParserTest extends \PHPUnit_Framework_TestCase {
    function testParser() {
        $parser = new UriParser();
        $this->assertEquals(
            array(
                'a' => array('x'),
                'b' => array('y', 'z'),
            ),
            $parser->parseUri('a=x/b=y,z')
        );
    }


    function testParserWithTranslator() {
        $parser = new UriParser();
        $parser->setTranslator(
            new Translator\StaticTranslator(
                'a', 'A', array('x' => 'X', 'y' => 'Y', 'z' => 'Z')
            )
        );
        $this->assertEquals(
            array(
                'a' => array('x', 'y', 'z')
            ),
            $parser->parseUri('A=X,Y,Z')
        );
    }


    function testParserWithCustomSeparators() {
        $parser = new UriParser(';', '|', '^');
        $this->assertEquals(
            array(
                'a' => array('x'),
                'b' => array('y', 'z'),
            ),
            $parser->parseUri('a|x;b|y^z')
        );
    }


    function testComposer() {
        $parser = new UriParser();
        $this->assertEquals(
            'a=x/b=y,z',
            $parser->composeUri(array(
                'a' => array('x'),
                'b' => array('y', 'z'),
            ))
        );
    }


    function testComposerWithTranslator() {
        $parser = new UriParser();
        $parser->setTranslator(
            new Translator\StaticTranslator(
                'a', 'A', array('x' => 'X', 'y' => 'Y', 'z' => 'Z')
            )
        );
        $this->assertEquals(
            'A=X,Y,Z',
            $parser->composeUri(array('a' => array('x', 'y', 'z')))
        );
    }


    function testComposerWithCustomSeparators() {
        $parser = new UriParser(';', '|', '^');
        $this->assertEquals(
            'a|x;b|y^z',
            $parser->composeUri(
                array(
                    'a' => array('x'),
                    'b' => array('y', 'z'),
                )
            )
        );
    }
}