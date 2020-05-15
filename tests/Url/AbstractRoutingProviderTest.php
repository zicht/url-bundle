<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url\Provider {
    class Impl extends \Zicht\Bundle\UrlBundle\Url\AbstractRoutingProvider
    {
        public function routing($object, array $options = [])
        {
            return ['a', ['b' => 'c']];
        }

        public function supports($object)
        {
            return true;
        }
    }
}

namespace ZichtTest\Bundle\UrlBundle\Url {

    use PHPUnit\Framework\TestCase;

    class AbstractRoutingProviderTest extends TestCase
    {
        public function testRouting()
        {
            $mock = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
            $provider = new \ZichtTest\Bundle\UrlBundle\Url\Provider\Impl($mock);
            $mock->expects($this->once())->method('generate')->with('a', ['b' => 'c'])->will($this->returnValue('baz'));
            $this->assertEquals('baz', $provider->url('foo'));
        }
    }
}
