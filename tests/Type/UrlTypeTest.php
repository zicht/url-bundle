<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Type;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Type\UrlType;

/**
 * @property UrlType $type
 */
class UrlTypeTest extends TestCase
{
    public function setUp(): void
    {
        $aliasing = $this->getMockBuilder(Aliasing::class)->disableOriginalConstructor()->getMock();
        $this->type = new UrlType($aliasing);
    }

    public function testGetBlockPrefix()
    {
        $this->assertEquals('zicht_url', $this->type->getBlockPrefix());
    }

    public function testGetParent()
    {
        $this->assertEquals('Zicht\Bundle\AdminBundle\Form\AutocompleteType', $this->type->getParent());
    }
}
