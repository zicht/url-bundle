<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Logging;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @property \Zicht\Bundle\UrlBundle\Logging\Logging $logging
 */
class LoggingTest extends TestCase
{
    public function setUp()
    {
        $this->manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $this->logging = new \Zicht\Bundle\UrlBundle\Logging\Logging($this->manager);
    }

    public function testCreateLog()
    {
        $req = new Request();
        $req->headers->add(
            [
                'referer' => 'foo',
                'user-agent' => 'bar',
            ]
        );
        $req->server->set('REMOTE_ADDR', '192.168.123.4');

        $log = $this->logging->createLog($req, 'Our neural pathways have become accustomed to your sensory input patterns.');

        $this->assertEquals('192.168.123.4', $log->getIp());
        $this->assertEquals('foo', $log->getReferer());
        $this->assertEquals('Our neural pathways have become accustomed to your sensory input patterns.', $log->getMessage());

        $this->manager->expects($this->once())->method('flush')->with($log);
        $this->manager->expects($this->once())->method('persist')->with($log);
        $this->logging->flush($log);
    }
}
