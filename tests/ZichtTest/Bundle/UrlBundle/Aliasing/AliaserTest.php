<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use Zicht\Bundle\UrlBundle\Aliasing\Aliaser;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Url\Provider;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

class AliaserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Aliasing
     */
    public $aliasing;

    /**
     * @var Aliaser
     */
    public $aliaser;

    /**
     * @var Provider
     */
    public $provider;

    public function setUp()
    {
        $this->aliasing = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Aliasing\Aliasing')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Url\StaticProvider')
            ->disableOriginalConstructor()
            ->setMethods(array('url'))
            ->getMock()
        ;

        $this->aliaser = new \Zicht\Bundle\UrlBundle\Aliasing\Aliaser($this->aliasing, $this->provider);
    }


    public function testAliasing()
    {
        $foo = 'wut';
        $this->provider->expects($this->once())->method('url')->with($foo)->will($this->returnValue('/baz/bat'));
        $this->aliaser->setConflictingInternalUrlStrategy(Aliasing::STRATEGY_MOVE_PREVIOUS_TO_NEW);

        $this->aliasing->expects($this->never())->method('hasPublicAlias');
        $this->aliasing->expects($this->once())->method('addAlias')->with(
            '/wut',
            '/baz/bat',
            UrlAlias::REWRITE,
            Aliasing::STRATEGY_SUFFIX,
            Aliasing::STRATEGY_MOVE_PREVIOUS_TO_NEW
        )->will($this->returnValue(true));
        $this->assertTrue($this->aliaser->createAlias($foo));
    }


    /**
     * @dataProvider strategyTypes
     */
    public function testAliaserConflictResolutionWillForwardToAliaser($type)
    {
        $foo = 'wut';
        $this->aliaser->setConflictingInternalUrlStrategy($type);
        $this->provider->expects($this->once())->method('url')->with($foo)->will($this->returnValue('/baz/bat'));
        $this->aliasing->expects($this->never())->method('hasPublicAlias');
        $this->aliasing->expects($this->once())->method('addAlias')->with(
            '/wut',
            '/baz/bat',
            UrlAlias::REWRITE,
            Aliasing::STRATEGY_SUFFIX,
            $type
        )->will($this->returnValue(false));
        $this->assertFalse($this->aliaser->createAlias($foo));
    }


    public function strategyTypes()
    {
        return [[Aliasing::STRATEGY_MOVE_PREVIOUS_TO_NEW], [Aliasing::STRATEGY_IGNORE]];
    }

    public function testReturningNullWillNotCallAliaser()
    {
        $foo = 'wut';
        $this->aliaser->setConflictingInternalUrlStrategy(Aliasing::STRATEGY_IGNORE);
        $this->provider->expects($this->once())->method('url')->with($foo)->will($this->returnValue('/baz/bat'));
        $this->aliasing->expects($this->never())->method('hasPublicAlias');
        $this->aliasing->expects($this->once())->method('addAlias')->with(
            '/wut',
            '/baz/bat',
            UrlAlias::REWRITE,
            Aliasing::STRATEGY_SUFFIX,
            Aliasing::STRATEGY_IGNORE
        )->will($this->returnValue(false));
        $this->assertFalse($this->aliaser->createAlias($foo));
    }


    public function testSetIsBatchDelegatesToAliaser()
    {
        $ret = function() {};
        $this->aliasing->expects($this->once())->method('setIsBatch')->with(true)->will($this->returnValue($ret));
        $this->assertEquals($ret, $this->aliaser->setIsBatch(true));
    }
}
