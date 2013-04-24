<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use \Zicht\Bundle\UrlBundle\Aliasing\ProviderDecorator;
/**
 * Test for ProviderDecorator
 */
class ProviderDecoratorTest extends \PHPUnit_Framework_TEstCAse
{
    public $aliasing;

    /**
     * Sets up an aliasing mock
     *
     * @return void
     */
    public function setUp()
    {
        $this->aliasing = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Aliasing\Aliasing')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test that the decoration will take place calling the Aliasing::hasPublicAlias method
     *
     * @return void
     */
    public function testDecoration()
    {
        $providerMock = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Url\Provider')->getMock();
        $providerMock->expects($this->once())->method('supports')->will($this->returnValue(true));
        $providerMock->expects($this->once())->method('url')->will($this->returnValue('some/url'));
        $this->aliasing
            ->expects($this->once())
            ->method('hasPublicAlias')
            ->with('some/url')
            ->will($this->returnValue('some/decorated/url'));
        $providerDecorator = new ProviderDecorator($this->aliasing);
        $providerDecorator->addProvider($providerMock);
        $this->assertEquals('some/decorated/url', $providerDecorator->url(new \stdClass));
    }


    /**
     * Throw an exception if none of the providers support the requested object
     *
     * @return void
     *
     * @expectedException Zicht\Bundle\UrlBundle\Exception\UnsupportedException
     */
    public function testUnsupportedUrlWillThrowException()
    {
        $providerMock = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Url\Provider')->getMock();
        $providerMock->expects($this->once())->method('supports')->will($this->returnValue(false));
        $providerDecorator = new ProviderDecorator($this->aliasing);
        $providerDecorator->addProvider($providerMock);
        $providerDecorator->url(new \stdClass);
    }
}