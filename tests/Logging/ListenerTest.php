<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Logging;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Zicht\Bundle\UrlBundle\Logging\Listener as LoggingListener;
use Zicht\Bundle\UrlBundle\Logging\Logging;

class ListenerTest extends TestCase
{
    public function testListener()
    {
        $msg = 'They ate grandma!';
        $req = new Request();
        $response = new Response();
        $response->setStatusCode(500);

        $logging = $this->getMockBuilder(Logging::class)->onlyMethods(['flush'])->disableOriginalConstructor()->getMock();
        $exceptionEvent = $this->getMockBuilder(ExceptionEvent::class)->disableOriginalConstructor()->getMock();
        $exceptionEvent->expects($this->once())->method('getThrowable')->willReturn(new \Exception($msg));
        $exceptionEvent->expects($this->once())->method('getRequest')->willReturn($req);

        $responseEvent = $this->getMockBuilder(ResponseEvent::class)->disableOriginalConstructor()->getMock();
        $responseEvent->expects($this->once())->method('getRequestType')->willReturn(HttpKernelInterface::MASTER_REQUEST);
        $responseEvent->expects($this->once())->method('getResponse')->willReturn($response);

//        $logging->expects($this->once())->method('createLog')->with($req)->will($this->returnValue($this->getMock('Zicht\Bundle\UrlBundle\Entity\ErrorLog', array(), array(), '', false)));

        $listener = new LoggingListener($logging);
        $value = null;

        $logging->expects($this->once())->method('flush')->willReturnCallback(
            function ($v) use (&$value) {
                $value = $v;
            }
        );

        $listener->onKernelException($exceptionEvent);
        $listener->onKernelResponse($responseEvent);
        $this->assertEquals($msg, $value->getMessage());
    }
}
