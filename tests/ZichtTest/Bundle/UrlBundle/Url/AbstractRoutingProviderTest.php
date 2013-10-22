<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url\Provider
{
    class Impl extends \Zicht\Bundle\UrlBundle\Url\AbstractRoutingProvider
    {
        public function routing($object, array $options = array())
        {
            return array('a', array('b' => 'c'));
        }

        public function supports($object)
        {
            return true;
        }
    }
}

namespace ZichtTest\Bundle\UrlBundle\Url
{
    class AbstractRoutingProviderTest extends \PHPUnit_Framework_TestCase
    {
        public function testRouting()
        {
            $mock = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
            $provider = new \ZichtTest\Bundle\UrlBundle\Url\Provider\Impl($mock);
            $mock->expects($this->once())->method('generate')->with('a', array('b' => 'c'))->will($this->returnValue('baz'));
            $this->assertEquals('baz', $provider->url('foo'));
        }
    }
}
