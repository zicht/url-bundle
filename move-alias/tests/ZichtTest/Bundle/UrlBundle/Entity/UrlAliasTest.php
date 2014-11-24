<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\UrlBundle\Entity;

use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

class UrlAliasTest extends \PHPUnit_Framework_TestCase
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