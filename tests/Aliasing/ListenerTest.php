<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Aliasing\Listener;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * @property Listener $listener
 * @property RouterListener $router
 * @property Aliasing|\PHPUnit_Framework_MockObject_MockObject $aliasing
 */
class ListenerTest extends TestCase
{
    public function setUp(): void
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
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::SUB_REQUEST));
        $event->expects($this->never())->method('getRequest');
        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestHandlesMasterRequest()
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MAIN_REQUEST));
        $event->expects($this->atLeastOnce())->method('getRequest')->will($this->returnValue(new Request()));
        $this->listener->onKernelRequest($event);
    }

    public function testRequestIsRoutedWithInternalUrl()
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $req = (new Generator())->getMock('Symfony\Component\HttpFoundation\Request', ['getRequestUri']);
        $publicUrl = '/foo';
        $req->expects($this->any())->method('getRequestUri')->will($this->returnValue($publicUrl));
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernel')->disableOriginalConstructor()->getMock();

        $this->aliasing->expects($this->once())->method('hasInternalAlias')->with($publicUrl)->will(
            $this->returnValue(
                new UrlAlias('/foo', '/bar', 0)
            )
        );
        $event->expects($this->any())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MAIN_REQUEST));
        $event->expects($this->atLeastOnce())->method('getRequest')->will($this->returnValue($req));
        $event->expects($this->any())->method('getKernel')->will($this->returnValue($kernel));

        $propagatedEvent = null;
        $this->router->expects($this->once())->method('onKernelRequest')->will(
            $this->returnCallback(
                function ($e) use (&$propagatedEvent) {
                    $propagatedEvent = $e;
                }
            )
        );
        $this->listener->onKernelRequest($event);
//        $this->assertEquals('/bar', $propagatedEvent->getRequest()->getRequestUri());
    }

    /**
     * @doesNotPerformAssertions
     * @throws \ReflectionException
     */
    public function testParameterParsing()
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $req = (new Generator())->getMock('Symfony\Component\HttpFoundation\Request', ['getRequestUri']);
        $publicUrl = '/foo/a=b,c';
        $this->listener->setIsParamsEnabled(true);
        $req->expects($this->any())->method('getRequestUri')->will($this->returnValue($publicUrl));
        $event->expects($this->any())->method('getRequest')->will($this->returnValue($req));
        $event->expects($this->any())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MAIN_REQUEST));
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernel')->disableOriginalConstructor()->getMock();
        $event->expects($this->any())->method('getKernel')->will($this->returnValue($kernel));

        $this->aliasing->expects($this->any())->method('hasInternalAlias')->with('/foo')->will(
            $this->returnValue(
                new UrlAlias('/foo', '/bar', 0)
            )
        );
        try {
            $this->listener->onKernelRequest($event);
        } catch (\Exception $e) {
        }
//        $this->assertEquals('/bar', $propagatedEvent->getRequest()->getRequestUri());
    }

    public function testParameterParsingSkipsToRoutingIfNoAliasExists()
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $req = (new Generator())->getMock('Symfony\Component\HttpFoundation\Request', ['getRequestUri']);
        $publicUrl = '/foo/a=b,c';
        $this->listener->setIsParamsEnabled(true);
        $req->expects($this->any())->method('getRequestUri')->will($this->returnValue($publicUrl));
        $event->expects($this->any())->method('getRequest')->will($this->returnValue($req));
        $event->expects($this->any())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MAIN_REQUEST));
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernel')->disableOriginalConstructor()->getMock();
        $event->expects($this->any())->method('getKernel')->will($this->returnValue($kernel));

        $this->aliasing->expects($this->any())->method('hasInternalAlias')->with('/foo')->will($this->returnValue(false));
        $this->router->expects($this->once())->method('onKernelRequest');
        $this->listener->onKernelRequest($event);
//        $this->assertEquals('/bar', $propagatedEvent->getRequest()->getRequestUri());
    }

    /**
     * @dataProvider exclusionCases
     * @param mixed $shouldRoute
     * @param mixed $pattern
     * @param mixed $publicUrl
     */
    public function testUrlExclusion($shouldRoute, $pattern, $publicUrl)
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->listener->setExcludePatterns([$pattern]);
        $req = (new Generator())->getMock('Symfony\Component\HttpFoundation\Request', ['getRequestUri']);
        $req->expects($this->any())->method('getRequestUri')->will($this->returnValue($publicUrl));
        $event->expects($this->any())->method('getRequest')->will($this->returnValue($req));
        $event->expects($this->any())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MAIN_REQUEST));

        if ($shouldRoute) {
            $this->aliasing->expects($this->atLeastOnce())->method('hasInternalAlias');
        } else {
            $this->aliasing->expects($this->never())->method('hasInternalAlias');
        }

        $this->listener->onKernelRequest($event);
    }

    public function exclusionCases()
    {
        return [
            [false, '/\bbat\b/', 'foo bar bat baz'],
            [true, '/\bqux\b/', 'foo bar bat baz'],
        ];
    }

    /**
     * @dataProvider statusModes
     * @param mixed $statusCode
     * @param mixed $expectsException
     */
    public function testRedirectModes($statusCode, $expectsException = false)
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MAIN_REQUEST));
        $event->expects($this->atLeastOnce())->method('getRequest')->will($this->returnValue(new Request()));
        $setResponseValue = null;
        $this->aliasing->expects($this->once())->method('hasInternalAlias')->will(
            $this->returnValue(
                new UrlAlias('/foo', '/bar', $statusCode)
            )
        );
        if ($expectsException) {
            $e = null;
            try {
                $this->listener->onKernelRequest($event);
            } catch (\Exception $e) {
            }
            $this->assertInstanceOf('UnexpectedValueException', $e);
        } else {
            $event->expects($this->once())->method('setResponse')->will(
                $this->returnCallback(
                    function ($response) use (&$setResponseValue) {
                        $setResponseValue = $response;
                    }
                )
            );
            $this->listener->onKernelRequest($event);

            $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $setResponseValue);
            $this->assertEquals($statusCode, $setResponseValue->getStatusCode());
            $this->assertEquals('/bar', $setResponseValue->headers->get('location'));
        }
    }

    public function testParameterParsingUtf8()
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request = new Request();
        $uri = '/%D0%9F%D1%80%D0%BE%D0%B4%D1%83%D0%BA%D1%86%D0%B8%D1%8F-%D1%84%D0%B8%D1%80%D0%BC%D1%8B-%E2%80%9C%D0%9C%D0%BE%D0%BA%D0%B2%D0%B5%D0%BB%D0%B4%E2%80%9D/products';
        $internal = '/ru/products/1';
        $request->server->set('REQUEST_URI', $uri);
        $this->listener->setIsParamsEnabled(true);
        $event->expects($this->any())->method('getRequest')->will($this->returnValue($request));
        $event->expects($this->any())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MAIN_REQUEST));
        $event->expects($this->any())->method('getKernel')->will($this->returnValue($this->getMockBuilder('Symfony\Component\HttpKernel\HttpKernel')->disableOriginalConstructor()->getMock()));
        $this->aliasing->expects($this->any())->method('hasInternalAlias')->willReturnCallback(function ($uri) use ($internal) {
            if ($uri === '/Продукция-фирмы-“Моквелд”/products') {
                return new UrlAlias('/Продукция-фирмы-“Моквелд”/products', $internal, 0);
            }
            return null;
        });
        $this->listener->onKernelRequest($event);
        $this->assertSame($uri, $request->server->get('ORIGINAL_REQUEST_URI'));
        $this->assertSame($internal, $request->server->get('REQUEST_URI'));
    }

    public function statusModes()
    {
        return [
            [UrlAlias::MOVE],
            [UrlAlias::ALIAS],
            [-1, true],
        ];
    }
}
