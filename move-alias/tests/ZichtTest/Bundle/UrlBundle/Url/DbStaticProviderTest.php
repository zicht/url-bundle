<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\UrlBundle\Url;

use Symfony\Component\HttpFoundation\Request;

class DbStaticProviderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $this->provider = new \Zicht\Bundle\UrlBundle\Url\DbStaticProvider($this->manager);
    }


    public function testLazyLoad()
    {
        $data = array();

        $r = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Entity\Repository\StaticReferenceRepository')->setMethods(array('getAll'))->disableOriginalConstructor()->getMock();

        $this->manager->expects($this->once())->method('getRepository')->with('ZichtUrlBundle:StaticReference')
            ->will($this->returnValue($r));

        $r->expects($this->once())->method('getAll')->with(null)->will($this->returnValue(array(
            new \Zicht\Bundle\UrlBundle\Entity\StaticReference(),
            new \Zicht\Bundle\UrlBundle\Entity\StaticReference()
        )));
//        $this->manager->expects($this->once())->method('get')
        $this->provider->supports('foo');
    }


    public function testRequestLocaleIsPassed()
    {
        $data = array();

        $r = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Entity\Repository\StaticReferenceRepository')->setMethods(array('getAll'))->disableOriginalConstructor()->getMock();

        $this->manager->expects($this->once())->method('getRepository')->with('ZichtUrlBundle:StaticReference')
            ->will($this->returnValue($r));

        $this->provider->setRequest($req = new Request);
        $req->attributes->set('_locale', 'klingon');

        $r->expects($this->once())->method('getAll')->with('klingon')->will($this->returnValue(array(
            new \Zicht\Bundle\UrlBundle\Entity\StaticReference('foo', array('klingon' => 'ptach', 'romulan' => 'jolantru')),
            new \Zicht\Bundle\UrlBundle\Entity\StaticReference('bar', array('klingon' => 'k\'pla', 'romulan' => 'rihiirin'))
        )));
        $this->assertTrue($this->provider->supports('foo'));
        $this->assertEquals('/ptach', $this->provider->url('foo'));
    }
}