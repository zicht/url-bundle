<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Entity;

use PHPUnit\Framework\TestCase;

class StaticReferenceTest extends TestCase
{
    public function testApi()
    {
        $ref = new \Zicht\Bundle\UrlBundle\Entity\StaticReference();

        $ref->addTranslations(new \Zicht\Bundle\UrlBundle\Entity\StaticReferenceTranslation('nl', 'foo'));
        $ref->addTranslations(new \Zicht\Bundle\UrlBundle\Entity\StaticReferenceTranslation('en', 'bar'));

        $this->assertFalse($ref->hasTranslation('fr'));

        $this->assertTrue((bool)$ref->hasTranslation('nl'));
        $this->assertEquals('foo', $ref->getTranslation('nl')->getUrl());

        $this->assertTrue((bool)$ref->hasTranslation('en'));
        $this->assertEquals('bar', $ref->getTranslation('en')->getUrl());

        (string)$ref; // must not fail.

        $ref->setMachineName('qux');
        $this->assertEquals('qux', $ref->getMachineName());

        (string)$ref; // must not fail.
    }
}
