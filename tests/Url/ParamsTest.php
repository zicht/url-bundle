<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Url\Params\Params;
use Zicht\Bundle\UrlBundle\Url\Params\Translator;
use Zicht\Bundle\UrlBundle\Url\Params\UriParser;

class ParamsTest extends TestCase
{
    function testInit()
    {
        $uri = new Params();
        $this->assertEquals([], $uri->toArray());
    }


    function testParsing()
    {
        $uri = new Params();
        $uri->setUri('a=b/c=d,e');
        $this->assertEquals(['a' => ['b'], 'c' => ['d', 'e']], $uri->toArray());
    }


    function testToString()
    {
        $uri = new Params();
        $uri->setUri('a=b/c=d');
        $this->assertEquals(['a' => ['b'], 'c' => ['d']], $uri->toArray());
    }


    function testToStringWillSortKeys()
    {
        $uri = new Params();
        $uri->setUri('c=d/a=b');
        $this->assertEquals('a=b/c=d', (string)$uri);
    }


    function testToStringWillSortValues()
    {
        $uri = new Params();
        $uri->setUri('c=e,d/a=b');
        $this->assertEquals('a=b/c=d,e', (string)$uri);
    }


    function testFacetedAdd()
    {
        $uri = new Params();
        $uri->setUri('a=b/c=d');
        $this->assertEquals('a=b/c=d,e', (string)$uri->with('c', 'e'));
    }


    function testNonMultipleWithWillThrowExceptionIfValueIsNotScalar()
    {
        $this->expectException('\InvalidArgumentException');
        $uri = new Params();
        $uri->setUri('a=b/c=d');
        $uri->with('c', ['e'], false);
    }


    function testWithout()
    {
        $uri = new Params();
        $uri->setUri('a=b/c=d');
        $this->assertEquals((string)'a=b', (string)$uri->without('c'));
    }


    function testWithoutArray()
    {
        $uri = new Params();
        $uri->setUri('a=b/c=d');
        $this->assertEquals('', (string)$uri->without(['a', 'c']));
    }


    function testFacetedRemove()
    {
        $uri = new Params();
        $uri->setUri('a=b/c=d,e');
        $this->assertEquals('a=b/c=d', (string)$uri->with('c', 'e'));
    }


    function testFacetedReplace()
    {
        $uri = new Params();
        $uri->setUri('a=b/c=d,e');
        $this->assertEquals('a=x/c=d,e', (string)$uri->with('a', 'x', false));
    }

    function testToggle()
    {
        $uri = new Params();
        $uri->setUri('c=d,e');
        $this->assertEquals('a=b/c=d,e', (string)$uri->with('a', 'b'));

        $uri = new Params();
        $uri->setUri('a=b/c=d,e');
        $this->assertEquals('c=d,e', (string)$uri->with('a', 'b'));
    }

    function testToggleNonMultiple()
    {
        $uri = new Params();
        $uri->setUri('');
        $this->assertEquals('a=b', (string)$uri->with('a', 'b', false));

        $uri = new Params();
        $uri->setUri('a=b');
        $this->assertEquals('', (string)$uri->with('a', 'b', false));

        $uri = new Params();
        $uri->setUri('c=d,e');
        $this->assertEquals('a=b/c=d,e', (string)$uri->with('a', 'b', false));

        $uri = new Params();
        $uri->setUri('a=b/c=d,e');
        $this->assertEquals('c=d,e', (string)$uri->with('a', 'b', false));

        // the same tests but now using setValues instead of setUri
        $uri = new Params();
        $uri->setValues([]);
        $this->assertEquals('a=b', (string)$uri->with('a', 'b', false));

        $uri = new Params();
        $uri->setValues(['a' => ['b']]);
        $this->assertEquals('', (string)$uri->with('a', 'b', false));

        $uri = new Params();
        $uri->setValues(['c' => ['d', 'e']]);
        $this->assertEquals('a=b/c=d,e', (string)$uri->with('a', 'b', false));

        $uri = new Params();
        $uri->setValues(['a' => ['b'], 'c' => ['d', 'e']]);
        $this->assertEquals('c=d,e', (string)$uri->with('a', 'b', false));
    }

    function testEmptyValues()
    {
        $uri = new Params();
        $uri->setUri('/=,');
        $this->assertEquals('', (string)$uri);
    }


    function testGetOne()
    {
        $uri = new Params();
        $uri->setUri('a=b,c');
        $this->assertEquals('b', $uri->getOne('a'));
    }


    function testWithWillNotMutateOriginal()
    {
        $uri = new Params();
        $uri->setUri('a=b,c');
        $uri->with('a', 'd');
        $this->assertEquals('a=b,c', (string)$uri);
    }


    function testWithoutWillNotMutateOriginal()
    {
        $uri = new Params();
        $uri->setUri('a=b,c,d');
        $uri->without('a');
        $this->assertEquals('a=b,c,d', (string)$uri);
    }


    function testTranslatedUri()
    {
        $date = date('Y-m-d');
        $tstamp = strtotime($date);

        $translator = new Translator\CompositeTranslator();
        $translator
            ->add(new Translator\StaticTranslator('keywords', 'gezocht-op', []))
            ->add(new Translator\StaticTranslator('terms', 'gefilterd-op', [1 => 'mies', 2 => 'noot', 3 => 'aap']))
            ->add(
                new Translator\CallbackTranslator(
                    'date',
                    'datum',
                    function ($s) {
                        return strtotime($s);
                    },
                    function ($timestamp) {
                        return date('Y-m-d', $timestamp);
                    }
                )
            );
        $parser = new UriParser();
        $parser->setTranslator($translator);
        $uri = new Params($parser);
        $uri->setUri('gezocht-op=rotterdam+en+omstreken/gefilterd-op=aap,noot,mies/datum=' . date('Y-m-d', $tstamp));

        $this->assertEquals(
            [
                'date' => [$tstamp],
                'keywords' => ['rotterdam en omstreken'],
                'terms' => [1, 2, 3],
            ],
            $uri->toArray()
        );
        $this->assertEquals('datum=' . $date . '/gezocht-op=rotterdam+en+omstreken/gefilterd-op=mies,noot,aap', (string)$uri);
    }
}
