<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\UrlBundle\Entity;

class StaticReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testApi()
    {
        $ref = new \Zicht\Bundle\UrlBundle\Entity\StaticReference();
    }
}