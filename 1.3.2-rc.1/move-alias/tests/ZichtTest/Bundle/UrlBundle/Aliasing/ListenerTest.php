<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use \Zicht\Bundle\UrlBundle\Aliasing\Listener;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use \Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\EventListener\RouterListener;

/**
 * @property Listener $listener
 * @property RouterListener $router
 */
class ListenerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->aliasing = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Aliasing\Aliasing')
            ->disableOriginalConstructor()
            ->getMock();
        $this->router = $this->getMockBuilder('Symfony\Component\HttpKernel\EventListener\RouterListener')
            ->disableOriginalConstructor()
            ->getMock();
        $this->listener = new Listener($this->aliasing, $this->router);
    }


    public function testOnKernelRequestDoesNotHandleSubRequest()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $event->expects($this->once())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::SUB_REQUEST));
        $event->expects($this->never())->method('getRequest');
        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestHandlesMasterRequest()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $event->expects($this->once())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
        $event->expects($this->atLeastOnce())->method('getRequest')->will($this->returnValue(new \Symfony\Component\HttpFoundation\Request()));
        $this->listener->onKernelRequest($event);
    }

    public function testRequestIsRoutedWithInternalUrl()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $req = $this->getMock('Symfony\Component\HttpFoundation\Request', array('getRequestUri'));
        $publicUrl = '/foo';
        $req->expects($this->any())->method('getRequestUri')->will($this->returnValue($publicUrl));
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernel')->disableOriginalConstructor()->getMock();

        $this->aliasing->expects($this->once())->method('hasInternalAlias')->with($publicUrl)->will($this->returnValue(
            new UrlAlias('/foo', '/bar', 0)
        ));
        $event->expects($this->any())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
        $event->expects($this->atLeastOnce())->method('getRequest')->will($this->returnValue($req));
        $event->expects($this->any())->method('getKernel')->will($this->returnValue($kernel));

        $propagatedEvent = null;
        $this->router->expects($this->once())->method('onKernelRequest')->will($this->returnCallback(function($e) use(&$propagatedEvent){
            $propagatedEvent = $e;
        }));
        $this->listener->onKernelRequest($event);
//        $this->assertEquals('/bar', $propagatedEvent->getRequest()->getRequestUri());
    }


    public function testParameterParsing()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $req = $this->getMock('Symfony\Component\HttpFoundation\Request', array('getRequestUri'));
        $publicUrl = '/foo/a=b,c';
        $this->listener->setIsParamsEnabled(true);
        $req->expects($this->any())->method('getRequestUri')->will($this->returnValue($publicUrl));
        $event->expects($this->any())->method('getRequest')->will($this->returnValue($req));
        $event->expects($this->any())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernel')->disableOriginalConstructor()->getMock();

        $this->aliasing->expects($this->any())->method('hasInternalAlias')->with('/foo')->will($this->returnValue(
            new UrlAlias('/foo', '/bar', 0)
        ));
        try {
            $this->listener->onKernelRequest($event);
        } catch(\Exception $e) {
        }
//        $this->assertEquals('/bar', $propagatedEvent->getRequest()->getRequestUri());
    }

    public function testParameterParsingSkipsToRoutingIfNoAliasExists()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $req = $this->getMock('Symfony\Component\HttpFoundation\Request', array('getRequestUri'));
        $publicUrl = '/foo/a=b,c';
        $this->listener->setIsParamsEnabled(true);
        $req->expects($this->any())->method('getRequestUri')->will($this->returnValue($publicUrl));
        $event->expects($this->any())->method('getRequest')->will($this->returnValue($req));
        $event->expects($this->any())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernel')->disableOriginalConstructor()->getMock();
        $event->expects($this->any())->method('getKernel')->will($this->returnValue($kernel));

        $this->aliasing->expects($this->any())->method('hasInternalAlias')->with('/foo')->will($this->returnValue(false));
        $this->router->expects($this->once())->method('onKernelRequest');
        $this->listener->onKernelRequest($event);
//        $this->assertEquals('/bar', $propagatedEvent->getRequest()->getRequestUri());
    }

    /**
     * @dataProvider exclusionCases
     */
    public function testUrlExclusion($shouldRoute, $pattern, $publicUrl)
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->listener->setExcludePatterns(array($pattern));
        $req = $this->getMock('Symfony\Component\HttpFoundation\Request', array('getRequestUri'));
        $req->expects($this->any())->method('getRequestUri')->will($this->returnValue($publicUrl));
        $event->expects($this->any())->method('getRequest')->will($this->returnValue($req));
        $event->expects($this->any())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        if ($shouldRoute) {
            $this->aliasing->expects($this->atLeastOnce())->method('hasInternalAlias');
        } else {
            $this->aliasing->expects($this->never())->method('hasInternalAlias');
        }

        $this->listener->onKernelRequest($event);
    }

    public function exclusionCases() {
        return array(
            array(false, '/\bbat\b/', 'foo bar bat baz'),
            array(true, '/\bqux\b/', 'foo bar bat baz'),
        );
    }


    /**
     * @dataProvider statusModes
     */
    public function testRedirectModes($statusCode, $expectsException = false)
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $event->expects($this->any())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
        $event->expects($this->atLeastOnce())->method('getRequest')->will($this->returnValue(new \Symfony\Component\HttpFoundation\Request()));
        $setResponseValue = null;
        $this->aliasing->expects($this->once())->method('hasInternalAlias')->will($this->returnValue(
            new UrlAlias('/foo', '/bar', $statusCode)
        ));
        if ($expectsException) {
            $e = null;
            try {
                $this->listener->onKernelRequest($event);
            } catch(\Exception $e) {
            }
            $this->assertInstanceOf('UnexpectedValueException', $e);
        } else {
            $event->expects($this->once())->method('setResponse')->will($this->returnCallback(function($response) use(&$setResponseValue){
                $setResponseValue = $response;
            }));
            $this->listener->onKernelRequest($event);

            $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $setResponseValue);
            $this->assertEquals($statusCode, $setResponseValue->getStatusCode());
            $this->assertEquals('/bar', $setResponseValue->headers->get('location'));
        }
    }
    public function statusModes()
    {
        return array(
            array(UrlAlias::MOVE),
            array(UrlAlias::ALIAS),
            array(-1, true),
        );
    }
}