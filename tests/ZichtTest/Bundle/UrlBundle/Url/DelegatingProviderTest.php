<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url\Provider {
    class MockSuggestProvider implements \Zicht\Bundle\UrlBundle\Url\Provider, \Zicht\Bundle\UrlBundle\Url\SuggestableProvider
    {
        public function __construct($suggestions)
        {
            $this->suggestions = $suggestions;
        }

        public function supports($object)
        {
        }

        public function url($object, array $options = array())
        {
        }

        public function suggest($pattern)
        {
            $this->pattern = $pattern;
            return $this->suggestions;
        }
    }
}

namespace ZichtTest\Bundle\UrlBundle\Url {
    class DelegatingProviderTest extends \PHPUnit_Framework_TestCase
    {
        public function testApi()
        {
            $provider = new \Zicht\Bundle\UrlBundle\Url\DelegatingProvider();
            $refs = array(
                array(
                    'a' => 'b',
                    'x' => 'y'
                ),
                array(
                    'c' => 'd',
                    'x' => 'z' // <-- this has higher priority (see 10 - $index further down)
                )
            );

            foreach (array_keys($refs) as $index) {

                $mock[$index] = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Url\Provider')->getMock();
                $mock[$index]
                    ->expects($this->any())
                    ->method('supports')->will($this->returnCallback(function ($o) use ($refs, $index) {
                        return isset($refs[$index][$o]);
                    }));
                $mock[$index]
                    ->expects($this->any())
                    ->method('url')->will($this->returnCallback(function ($o) use ($refs, $index) {
                        return $refs[$index][$o];
                    }));
                $provider->addProvider($mock[$index], 10 - $index);
            }

            $this->assertTrue($provider->supports('a'));
            $this->assertTrue($provider->supports('c'));
            $this->assertTrue($provider->supports('x'));
            $this->assertFalse($provider->supports('not-supported-'));

            $this->assertEquals('b', $provider->url('a'));
            $this->assertEquals('d', $provider->url('c'));
            $this->assertEquals('z', $provider->url('x'));
        }


        /**
         * @expectedException \Zicht\Bundle\UrlBundle\Exception\UnsupportedException
         */
        public function testUnsupported()
        {
            $provider = new \Zicht\Bundle\UrlBundle\Url\DelegatingProvider();

            $provider->url('foo');
        }

        public function testSuggestDelegatesToAllRegisteredProviders()
        {
            $provider = new \Zicht\Bundle\UrlBundle\Url\DelegatingProvider();

            $provider->addProvider(new \ZichtTest\Bundle\UrlBundle\Url\Provider\MockSuggestProvider(array('foo')));
            $provider->addProvider(new \ZichtTest\Bundle\UrlBundle\Url\Provider\MockSuggestProvider(array('bar')));

            $expected = array('foo', 'bar');
            $suggestions = $provider->suggest('baz');
            sort($expected);
            sort($suggestions);
            $this->assertEquals($expected, $suggestions);
        }
    }
}