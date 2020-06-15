<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Aliasing\Listener;
use Zicht\Bundle\UrlBundle\Aliasing\PublicAliasHandler;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\EventListener\RouterListener;

class ListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Aliasing|\PHPUnit_Framework_MockObject_MockObject */
    private $aliasing;

    /** @var RouterListener|\PHPUnit_Framework_MockObject_MockObject */
    private $router;

    /** @var PublicAliasHandler|\PHPUnit_Framework_MockObject_MockObject */
    private $publicAliasHandler;

    /** @var Listener */
    private $listener;

    public function setUp()
    {
        $this->aliasing = $this->getMockBuilder(Aliasing::class)->disableOriginalConstructor()->getMock();
        $this->router = $this->getMockBuilder(RouterListener::class)->disableOriginalConstructor()->getMock();
        $this->publicAliasHandler = $this->getMockBuilder(PublicAliasHandler::class)->disableOriginalConstructor()->getMock();
        $this->listener = new Listener($this->aliasing, $this->router, $this->publicAliasHandler);
    }

    public function testOnKernelRequestDoesNotHandleSubRequest()
    {
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getRequestType')->willReturn(HttpKernelInterface::SUB_REQUEST);
        $this->publicAliasHandler->expects($this->never())->method('handlePublicUrl');
        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestHandlesMasterRequest()
    {
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getRequestType')->willReturn(HttpKernelInterface::MASTER_REQUEST);
        $event->method('getRequest')->willReturn($this->getMock(Request::class, ['getRequestUri']));
        $this->publicAliasHandler->expects($this->once())->method('handlePublicUrl');
        $this->listener->onKernelRequest($event);
    }

    /**
     * @dataProvider redirectResponses
     * @param string $publicUrl
     * @param UrlAlias $urlAlias
     */
    public function testRedirectingAliasLeadsToCorrectRedirectResponse($publicUrl, $urlAlias)
    {
        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getRequestType')->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request = $this->getMock(Request::class, ['getRequestUri']);
        $request->method('getRequestUri')->willReturn($publicUrl);
        $event->method('getRequest')->willReturn($request);

        $this->publicAliasHandler->expects($this->once())->method('handlePublicUrl')->with($publicUrl)->willReturn($urlAlias);

        /** @var RedirectResponse|null $response */
        $response = null;
        $event->expects($this->once())->method('setResponse')->willReturnCallback(function ($argument) use (&$response){
            $response = $argument;
        });
        $this->listener->onKernelRequest($event);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals($urlAlias->getMode(), $response->getStatusCode());
        $this->assertEquals($urlAlias->getInternalUrl(), $response->getTargetUrl());
    }

    /**
     * @return array[]
     */
    public function redirectResponses()
    {
        return [
            ['/redirect-permanent', new UrlAlias('/redirect-permanent', '/bar-1', UrlAlias::MOVE)],
            ['/redirect-temporarily', new UrlAlias('/redirect-temporarily', '/bar-2', UrlAlias::ALIAS)],
        ];
    }

    public function testRequestUriIsDecodedCorrectlyBeforeUse()
    {
        $uriEncoded = '/%D1%80%D1%83%D1%81%D1%81%D0%BA%D0%B8%D0%B9-url?xyz=%3Fabc%3D123';
        $uriDecoded = '/русский-url?xyz=%3Fabc%3D123';

        /** @var GetResponseEvent|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMockBuilder(GetResponseEvent::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('getRequestType')->willReturn(HttpKernelInterface::MASTER_REQUEST);

        $request = $this->getMock(Request::class, ['getRequestUri']);
        $request->method('getRequestUri')->willReturn($uriEncoded);
        $event->method('getRequest')->willReturn($request);

        $this->publicAliasHandler->expects($this->once())->method('handlePublicUrl')->with($uriDecoded);

        $this->listener->onKernelRequest($event);
    }
}
