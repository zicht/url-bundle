<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\UrlBundle\Url;

use Zicht\Bundle\UrlBundle\Url\Params\Params;
use Zicht\Bundle\UrlBundle\Url\Params\UriParser;
use Zicht\Bundle\UrlBundle\Url\Params\Translator;
 
class ParamsTest extends \PHPUnit_Framework_TestCase {
    function testInit() {
        $uri = new Params();
        $this->assertEquals(array(), $uri->toArray());
    }


    function testParsing() {
        $uri = new Params();
        $uri->setUri('a=b/c=d,e');
        $this->assertEquals(array('a' => array('b'), 'c' => array('d', 'e')), $uri->toArray());
    }


    function testToString() {
        $uri = new Params();
        $uri->setUri('a=b/c=d');
        $this->assertEquals(array('a' => array('b'), 'c' => array('d')), $uri->toArray());
    }


    function testToStringWillSortKeys() {
        $uri = new Params();
        $uri->setUri('c=d/a=b');
        $this->assertEquals('a=b/c=d', (string)$uri);
    }


    function testToStringWillSortValues() {
        $uri = new Params();
        $uri->setUri('c=e,d/a=b');
        $this->assertEquals('a=b/c=d,e', (string)$uri);
    }

    
    function testFacetedAdd() {
        $uri = new Params();
        $uri->setUri('a=b/c=d');
        $this->assertEquals('a=b/c=d,e', (string)$uri->with('c', 'e'));
    }


    /**
     * @expectedException InvalidArgumentException
     */
    function testNonMultipleWithWillThrowExceptionIfValueIsNotScalar() {
        $uri = new Params();
        $uri->setUri('a=b/c=d');
        $uri->with('c', array('e'), false);
    }


    function testWithout() {
        $uri = new Params();
        $uri->setUri('a=b/c=d');
        $this->assertEquals((string)'a=b', (string)$uri->without('c'));
    }


    function testWithoutArray() {
        $uri = new Params();
        $uri->setUri('a=b/c=d');
        $this->assertEquals('', (string)$uri->without(array('a', 'c')));
    }


    function testFacetedRemove() {
        $uri = new Params();
        $uri->setUri('a=b/c=d,e');
        $this->assertEquals('a=b/c=d', (string)$uri->with('c', 'e'));
    }


    function testFacetedReplace() {
        $uri = new Params();
        $uri->setUri('a=b/c=d,e');
        $this->assertEquals('a=x/c=d,e', (string)$uri->with('a', 'x', false));
    }


    function testEmptyValues() {
        $uri = new Params();
        $uri->setUri('/=,');
        $this->assertEquals('', (string)$uri);
    }


    function testGetOne() {
        $uri = new Params();
        $uri->setUri('a=b,c');
        $this->assertEquals('b', $uri->getOne('a'));
    }


    function testWithWillNotMutateOriginal() {
        $uri = new Params();
        $uri->setUri('a=b,c');
        $uri->with('a', 'd');
        $this->assertEquals('a=b,c', (string)$uri);
    }


    function testWithoutWillNotMutateOriginal() {
        $uri = new Params();
        $uri->setUri('a=b,c,d');
        $uri->without('a');
        $this->assertEquals('a=b,c,d', (string)$uri);
    }


    function testTranslatedUri() {
        $date = date('Y-m-d');
        $tstamp = strtotime($date);
        
        $translator = new Translator\CompositeTranslator();
        $translator
                ->add(new Translator\StaticTranslator('keywords', 'gezocht-op', array()))
                ->add(new Translator\StaticTranslator('terms', 'gefilterd-op', array(1 => 'mies', 2 => 'noot', 3 => 'aap')))
                ->add(
                    new Translator\CallbackTranslator(
                        'date',
                        'datum',
                        function($s) { return strtotime($s); },
                        function($timestamp) { return date('Y-m-d', $timestamp); }
                    )
                )
        ;
        $parser = new UriParser();
        $parser->setTranslator($translator);
        $uri = new Params($parser);
        $uri->setUri('gezocht-op=rotterdam+en+omstreken/gefilterd-op=aap,noot,mies/datum=' . date('Y-m-d', $tstamp));

        $this->assertEquals(
            array(
                'date' => array($tstamp),
                'keywords' => array('rotterdam en omstreken'),
                'terms' => array(1, 2, 3),
            ),
            $uri->toArray()
        );
        $this->assertEquals('datum=' . $date . '/gezocht-op=rotterdam+en+omstreken/gefilterd-op=mies,noot,aap', (string)$uri);
    }
}