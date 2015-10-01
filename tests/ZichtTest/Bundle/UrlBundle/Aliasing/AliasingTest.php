<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

class AliasingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Aliasing
     */
    public $aliasing;


    public function setUp()
    {
        $this->repos = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Aliasing\UrlAliasRepositoryInterface')
            ->setMethods(array('findOneBy', 'findAll', 'findBy'))
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('persist', 'flush', 'getRepository'))
            ->getMock();

        $this->manager->expects($this->any())->method('getRepository')->with('ZichtUrlBundle:UrlAlias')->will($this->returnValue($this->repos));
        $this->aliasing = new Aliasing($this->manager);
    }


    public function testHasInternalAlias()
    {
        $internal = $this->repos->expects($this->once())->method('findOneBy')->with(array(
            'public_url' => 'foo'
        ))->will($this->returnValue(new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('foo', 'bar')));
        $this->assertEquals('bar', $this->aliasing->hasInternalAlias('foo'));
    }

    public function testHasInternalAliasAsObject()
    {
        $entity   = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('foo', 'bar');
        $internal = $this->repos->expects($this->once())->method('findOneBy')->with(array(
            'public_url' => 'foo'
        ))->will($this->returnValue($entity));
        $this->assertEquals($entity, $this->aliasing->hasInternalAlias('foo', true));
    }

    public function testHasInternalAliasWithModeParameter()
    {
        $entity   = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('foo', 'bar', 301);
        $internal = $this->repos->expects($this->once())->method('findOneBy')->with(array(
            'public_url' => 'foo',
            'mode' => 301
        ))->will($this->returnValue($entity));
        $this->assertEquals($entity, $this->aliasing->hasInternalAlias('foo', true, 301));
    }

    public function testHasPublicAlias()
    {
        $internal = $this->repos->expects($this->once())->method('findOneBy')->with(array(
            'internal_url' => 'foo',
            'mode' => 0
        ))->will($this->returnValue(new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('bar', 'foo')));
        $this->assertEquals('bar', $this->aliasing->hasPublicAlias('foo'));
    }

    public function testHasPublicAliasAsObject()
    {
        $entity   = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('bar', 'foo');
        $internal = $this->repos->expects($this->once())->method('findOneBy')->with(array(
            'internal_url' => 'foo',
            'mode' => 0
        ))->will($this->returnValue($entity));
        $this->assertEquals($entity, $this->aliasing->hasPublicAlias('foo', true));
    }


    public function testAddAliasOverwrite()
    {
        $entity = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('foo', 'bar');
        $this->repos->expects($this->once())->method('findOneBy')->with(array(
            'public_url' => 'foo'
        ))->will($this->returnValue($entity));
        $this->repos->expects($this->once())->method('findBy')->with(array(
            'internal_url' => 'bat',
        ))->will($this->returnValue(array()));

        $this->manager->expects($this->once())->method('persist');
        $this->manager->expects($this->once())->method('flush');

        $this->aliasing->addAlias('foo', 'bat', 0, Aliasing::STRATEGY_OVERWRITE);
    }

    public function testAddAliasKeep()
    {
        $entity = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('foo', 'bar');
        $this->repos->expects($this->once())->method('findOneBy')->with(array(
            'public_url' => 'foo'
        ))->will($this->returnValue($entity));
        $this->repos->expects($this->once())->method('findBy')->with(array(
            'internal_url' => 'bat',
        ))->will($this->returnValue(array()));

        $this->manager->expects($this->never())->method('persist');
        $this->manager->expects($this->never())->method('flush');

        $this->aliasing->addAlias('foo', 'bat', 0, Aliasing::STRATEGY_KEEP);
    }

    public function testAddAliasSuffix()
    {
        $entity = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('foo', 'bar');
        $this->repos->expects($this->at(0))->method('findOneBy')->with(array(
            'public_url' => 'foo'
        ))->will($this->returnValue($entity));
//        $this->repos->expects($this->at(1))->method('findOneBy')->with(array(
//            'public_url' => 'foo-1'
//        ))->will($this->returnValue(null));
        $this->repos->expects($this->once())->method('findBy')->with(array(
            'internal_url' => 'bat',
        ))->will($this->returnValue(array()));

        $this->manager->expects($this->once())->method('persist');
        $this->manager->expects($this->once())->method('flush');

        $this->aliasing->addAlias('foo', 'bat', 0, Aliasing::STRATEGY_SUFFIX);
    }

    public function testAddAliasNew()
    {
        $entity = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('foo', 'bar');
        $this->repos->expects($this->at(0))->method('findOneBy')->with(array(
            'public_url' => 'foo'
        ))->will($this->returnValue(null));

        $this->manager->expects($this->once())->method('persist');
        $this->manager->expects($this->once())->method('flush');

        $this->aliasing->addAlias('foo', 'bat', 0, Aliasing::STRATEGY_SUFFIX);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddAliasInvalidStrategy()
    {
        $this->repos->expects($this->at(0))->method('findOneBy')->with(array(
            'public_url' => 'foo'
        ))->will($this->returnValue(new \Zicht\Bundle\UrlBundle\Entity\UrlAlias()));
        $this->repos->expects($this->once())->method('findBy')->with(array(
            'internal_url' => 'bat',
        ))->will($this->returnValue(array()));
        $this->aliasing->addAlias('foo', 'bat', 0, -1);
    }

    public function testAddAliasMovePreviousToNew()
    {
        $prevEntity = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('foo-previous', 'bar');
        $entity = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('foo-new', 'bar');
        $this->repos->expects($this->at(0))->method('findOneBy')->with(array(
            'internal_url' => 'bar',
            'mode' => UrlAlias::REWRITE,
        ))->will($this->returnValue($prevEntity));
        $this->repos->expects($this->at(1))->method('findOneBy')->with(array(
            'public_url' => 'foo-new',
        ))->will($this->returnValue($entity));

        $this->manager->expects($this->exactly(2))->method('persist');
        $this->manager->expects($this->exactly(2))->method('flush');

        $this->aliasing->addAlias('foo-new', 'bar', UrlAlias::REWRITE, Aliasing::STRATEGY_KEEP, Aliasing::STRATEGY_MOVE_PREVIOUS_TO_NEW);
    }

    /**
     * When AddAlias is called but current alias is exactly the same as the alias that we are trying to add, then nothing should happen.
     */
    public function testAddAliasNoChangesNeeded()
    {
        $entity = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('public-url', 'internal-url', UrlAlias::REWRITE);
        $this->repos->expects($this->at(0))->method('findOneBy')->with(array(
            'internal_url' => 'internal-url',
            'mode' => UrlAlias::REWRITE,
        ))->will($this->returnValue($entity));
        $this->repos->expects($this->at(1))->method('findOneBy')->with(array(
            'public_url' => 'public-url',
        ))->will($this->returnValue($entity));

        $this->manager->expects($this->never())->method('persist');
        $this->manager->expects($this->never())->method('flush');

        $this->aliasing->addAlias('public-url', 'internal-url', UrlAlias::REWRITE, Aliasing::STRATEGY_OVERWRITE, Aliasing::STRATEGY_MOVE_PREVIOUS_TO_NEW);
    }

    public function testBatchProcessingWithoutFlush()
    {
        $callback = $this->aliasing->setIsBatch(true);
        $this->assertTrue(is_callable($callback));
        $this->manager->expects($this->never())->method('flush');
    }

    public function testBatchProcessingWithFlush()
    {
        $callback = $this->aliasing->setIsBatch(true);
        $this->manager->expects($this->once())->method('flush');
        call_user_func($callback);
    }


    public function testAddAliasNewWithBatchProcessingWillPersistButNotFlush()
    {
        $this->aliasing->setIsBatch(true);
        $entity = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('foo', 'bar');
        $this->repos->expects($this->at(0))->method('findOneBy')->with(array(
            'public_url' => 'foo'
        ))->will($this->returnValue(null));

        $this->manager->expects($this->once())->method('persist');
        $this->manager->expects($this->never())->method('flush');

        $this->aliasing->addAlias('foo', 'bat', 0, Aliasing::STRATEGY_SUFFIX);
    }

    public function testAddAliasNewWithBatchProcessingWillKeepPublicUrl()
    {
        $this->aliasing->setIsBatch(true);
        $entity = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('foo', 'bar');
        $this->repos->expects($this->at(0))->method('findOneBy')->with(array(
            'public_url' => 'foo'
        ))->will($this->returnValue(null));

        $this->aliasing->addAlias('foo', 'bat', 0, Aliasing::STRATEGY_SUFFIX);
        $this->assertTrue((bool)$this->aliasing->hasInternalAlias('foo'));
    }

    public function testAddAliasNewWithBatchProcessingWillRespectSuffixing()
    {
        $this->aliasing->setIsBatch(true);

        $entity = new \Zicht\Bundle\UrlBundle\Entity\UrlAlias('foo', 'bar');
        $this->repos->expects($this->at(0))->method('findOneBy')->with(array(
            'public_url' => 'foo'
        ))->will($this->returnValue(null));
//        $this->repos->expects($this->at(1))->method('findOneBy')->with(array(
//            'public_url' => 'foo-1'
//        ))->will($this->returnValue(null));
        $this->repos->expects($this->once())->method('findBy')->with(array(
            'internal_url' => 'bar',
        ))->will($this->returnValue(array()));

        $list = array();
        $this->manager->expects($this->any())->method('persist')->will($this->returnCallback(function($a) use(&$list) {
            $list[]= $a->getPublicUrl();
        }));
        $this->manager->expects($this->never())->method('flush');

        $this->aliasing->addAlias('foo', 'bat', 0, Aliasing::STRATEGY_SUFFIX);
        $this->aliasing->addAlias('foo', 'bar', 0, Aliasing::STRATEGY_SUFFIX);

        $this->assertEquals(array('foo', 'foo-1'), $list);
    }
}
