<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Entity;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

class UrlAliasTest extends TestCase
{
    public function testApi()
    {
        $a = new UrlAlias('/foo', '/bar', 301);
        $this->assertEquals('/foo', $a->getPublicUrl());
        $this->assertEquals('/bar', $a->getInternalUrl());
        $this->assertEquals(301, $a->getMode());

        $this->assertTrue(is_string((string)$a));

        $a->getId();
    }
}
