<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Entity;

use PHPUnit\Framework\TestCase;

class ErrorLogTest extends TestCase
{
    public function testApi()
    {
        $e = new \Zicht\Bundle\UrlBundle\Entity\ErrorLog('foo', $now = new \DateTime(), 'bar', 'baz', 'bat', '/qux');

        $this->assertEquals('foo', $e->getMessage());
        $this->assertEquals($now, $e->getDateCreated());
        $this->assertEquals('bar', $e->getReferer());
        $this->assertEquals('baz', $e->getUa());
        $this->assertEquals('bat', $e->getIp());
        $this->assertEquals('/qux', $e->getUrl());

        $e->setStatus(404);
        $this->assertEquals(404, $e->getStatus());

        $this->assertTrue(is_string((string)$e));

        $e->getId();
    }
}
