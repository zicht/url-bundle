<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Logging;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ListenerTest extends TestCase
{
    public function testListener()
    {
        $msg = 'They ate grandma!';
        $req = new \Symfony\Component\HttpFoundation\Request();
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setStatusCode(500);

        $logging = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Logging\Logging')->setMethods(['flush'])->disableOriginalConstructor()->getMock();
        $exceptionEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')->disableOriginalConstructor()->getMock();
        $exceptionEvent->expects($this->once())->method('getException')->will($this->returnValue(new \Exception($msg)));
        $exceptionEvent->expects($this->once())->method('getRequest')->will($this->returnValue($req));

        $responseEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')->disableOriginalConstructor()->getMock();
        $responseEvent->expects($this->once())->method('getRequestType')->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));
        $responseEvent->expects($this->once())->method('getResponse')->will($this->returnValue($response));

//        $logging->expects($this->once())->method('createLog')->with($req)->will($this->returnValue($this->getMock('Zicht\Bundle\UrlBundle\Entity\ErrorLog', array(), array(), '', false)));

        $listener = new \Zicht\Bundle\UrlBundle\Logging\Listener($logging);
        $value = null;

        $logging->expects($this->once())->method('flush')->will(
            $this->returnCallback(
                function ($v) use (&$value) {
                    $value = $v;
                }
            )
        );

        $listener->onKernelException($exceptionEvent);
        $listener->onKernelResponse($responseEvent);
        $this->assertEquals($msg, $value->getMessage());
    }
}
