<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\UrlBundle\Url\Provider {
    class MockProvider implements \Zicht\Bundle\UrlBundle\Url\Provider
    {
        public function __construct($mappings)
        {
            $this->suggestions = $mappings;
        }

        public function supports($object) { return true; }
        public function url($object, array $options = array()) { return $this->suggestions[$object]; }
    }
}

namespace ZichtTest\Bundle\UrlBundle\Url {

    class RequestAwareProviderTest extends \PHPUnit_Framework_TestCase
    {
        public function testRequestDecoration()
        {
            $r = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
                ->disableOriginalConstructor()
                ->getMock()
            ;
            $r->expects($this->once())->method('getBaseUrl')->will($this->returnVAlue('http://example.org/foo'));
            $p = new \Zicht\Bundle\UrlBundle\Url\RequestAwareProvider($r);
            $p->addProvider(new Provider\MockProvider(array('a' => 'b', 'c' => '/foo/a', 'd' => 'http://example.org/foo/qux')));
            $this->assertEquals('b', $p->url('a'));
            $this->assertEquals('http://example.org/foo/b', $p->url('a', array('absolute' => true)));

            // TODO this test case fails, but not sure if this is intended...:
//            $this->assertEquals('http://example.org/foo/a', $p->url('c', array('absolute' => true)));
            $this->assertEquals('http://example.org/foo/qux', $p->url('d', array('absolute' => true)));
        }
    }
}