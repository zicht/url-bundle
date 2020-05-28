<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use Symfony\Component\HttpFoundation\Request;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Aliasing\Listener;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\EventListener\RouterListener;

/**
 * @property Listener $listener
 * @property RouterListener $router
 * @property Aliasing|\PHPUnit_Framework_MockObject_MockObject $aliasing
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

        $this->aliasing->expects($this->once())->method('getInternalAliases')->with([$publicUrl])->willReturn([$publicUrl => new UrlAlias('/foo', '/bar', 0)]);
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
        $event->expects($this->any())->method('getKernel')->will($this->returnValue($this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernel')->disableOriginalConstructor()->getMock()));
        $this->aliasing->expects($this->any())->method('getInternalAliases')->with(['/foo'])->willReturn(['/foo' => new UrlAlias('/foo', '/bar', 0)]);
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

        $this->aliasing->expects($this->any())->method('getInternalAliases')->with(['/foo'])->willReturn([]);
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
            $this->aliasing->expects($this->atLeastOnce())->method('getInternalAliases');
        } else {
            $this->aliasing->expects($this->never())->method('getInternalAliases');
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
        $req = $this->getMock('Symfony\Component\HttpFoundation\Request', array('getRequestUri'));
        $publicUrl = '/foo';
        $req->expects($this->any())->method('getRequestUri')->will($this->returnValue($publicUrl));
        $event->expects($this->atLeastOnce())->method('getRequest')->willReturn($req);
        $setResponseValue = null;
        $this->aliasing->expects($this->once())->method('getInternalAliases')->willReturn([$publicUrl => new UrlAlias($publicUrl, '/bar', $statusCode)]);
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

    public function testParameterParsingUtf8()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $request = new Request();
        $uriEncoded = '/%D0%9F%D1%80%D0%BE%D0%B4%D1%83%D0%BA%D1%86%D0%B8%D1%8F-%D1%84%D0%B8%D1%80%D0%BC%D1%8B-%E2%80%9C%D0%9C%D0%BE%D0%BA%D0%B2%D0%B5%D0%BB%D0%B4%E2%80%9D/products';
        $internal = '/ru/products/1';
        $request->server->set('REQUEST_URI', $uriEncoded);
        $this->listener->setIsParamsEnabled(true);
        $event->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $event->expects($this->any())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
        $event->expects($this->any())->method('getKernel')->will($this->returnValue($this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernel')->disableOriginalConstructor()->getMock()));
        $this->aliasing->expects($this->any())->method('getInternalAliases')->willReturnCallback(function($uri) use($internal) {
            $uriDecoded = '/Продукция-фирмы-“Моквелд”/products';
            if ($uri[0] === $uriDecoded) {
                return [$uriDecoded => new UrlAlias($uriDecoded, $internal, 0)];
            }
            return null;
        });
        $this->listener->onKernelRequest($event);
        $this->assertSame($uriEncoded, $request->server->get('ORIGINAL_REQUEST_URI'));
        $this->assertSame($internal, $request->server->get('REQUEST_URI'));
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
