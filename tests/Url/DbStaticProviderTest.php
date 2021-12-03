<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DbStaticProviderTest extends TestCase
{
    public function setUp(): void
    {
        $this->manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $this->stack = new RequestStack();
        $this->provider = new \Zicht\Bundle\UrlBundle\Url\DbStaticProvider($this->manager, $this->stack);
    }


    public function testLazyLoad()
    {
        $data = [];

        $r = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Entity\Repository\StaticReferenceRepository')->setMethods(['getAll'])->disableOriginalConstructor()->getMock();

        $this->manager->expects($this->once())->method('getRepository')->with('ZichtUrlBundle:StaticReference')
            ->will($this->returnValue($r));

        $r->expects($this->once())->method('getAll')->with(null)->will(
            $this->returnValue(
                [
                    new \Zicht\Bundle\UrlBundle\Entity\StaticReference(),
                    new \Zicht\Bundle\UrlBundle\Entity\StaticReference()
                ]
            )
        );
//        $this->manager->expects($this->once())->method('get')
        $this->provider->supports('foo');
    }


    public function testRequestLocaleIsPassed()
    {
        $data = [];

        $r = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Entity\Repository\StaticReferenceRepository')->setMethods(['getAll'])->disableOriginalConstructor()->getMock();

        $this->manager->expects($this->once())->method('getRepository')->with('ZichtUrlBundle:StaticReference')
            ->will($this->returnValue($r));

        $this->stack->push($req = new Request);
        $req->attributes->set('_locale', 'klingon');

        $r->expects($this->once())->method('getAll')->with('klingon')->will(
            $this->returnValue(
                [
                    new \Zicht\Bundle\UrlBundle\Entity\StaticReference('foo', ['klingon' => 'ptach', 'romulan' => 'jolantru']),
                    new \Zicht\Bundle\UrlBundle\Entity\StaticReference('bar', ['klingon' => 'k\'pla', 'romulan' => 'rihiirin'])
                ]
            )
        );
        $this->assertTrue($this->provider->supports('foo'));
        $this->assertEquals('/ptach', $this->provider->url('foo'));
    }
}
