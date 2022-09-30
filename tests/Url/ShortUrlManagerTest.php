<?php
declare(strict_types=1);
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Entity\Repository\UrlAliasRepository;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Url\ShortUrlHashGeneratorInterface;
use Zicht\Bundle\UrlBundle\Url\ShortUrlManager;

class ShortUrlManagerTest extends TestCase
{
    public function testCreateWithHashCollision()
    {
        // 1. arrange
        $url = '/url/to/page/999';
        // our fixed impl. of ShortUrlHashGenerator will return these hashes
        $dummy1 = new UrlAlias('iNXi8iaB', '/not-url/to/page/999', UrlAlias::MOVE);
        $dummy2 = new UrlAlias('iNXi8iaBT', '/also-not-url/to/page/999', UrlAlias::MOVE);

        $repository = self::getMockBuilder(UrlAliasRepository::class)->disableOriginalConstructor()->getMock();
        // mock 2 cycles by implementing the willReturn with a dummy with a url different from $url
        $repository->expects($this->at(0))->method('findOneByPublicUrl')->willReturn($dummy1);
        $repository->expects($this->at(1))->method('findOneByPublicUrl')->willReturn($dummy2);
        $repository->expects($this->at(2))->method('findOneByPublicUrl')->willReturn(null);

        $aliasing = self::getMockBuilder(Aliasing::class)->disableOriginalConstructor()->getMock();
        $aliasing->method('getRepository')->willReturn($repository);
        // the ->with() is the actual test...
        $aliasing->expects($this->once())->method('addAlias')->with('iNXi8iaBTX', $url, UrlAlias::MOVE);
        $aliasing->expects($this->once())->method('findAlias');

        $generator = new class() implements ShortUrlHashGeneratorInterface {
            public function generate($url, $length)
            {
                return substr('iNXi8iaBTXVsm0b0aynrlXekpB7Vxbz5MOG3dkgyzOsMWrQygmUKD1axjV5Wqpa', 0, $length);
            }
        };

        // 2. act
        $manager = new ShortUrlManager($aliasing, $generator);
        $manager->getAlias($url);

        // 3. assert
        // due to mocking Aliasing we have no return value
    }

    public function testCreateWithHashCollisionPreventingEndlessLoop()
    {
        // 3. assert
        $this->expectException(\LogicException::class);

        // 1. arrange
        $url = '/url/to/page/999';
        $dummy = new UrlAlias('iNXi8iaB', '/not-url/to/page/999', UrlAlias::MOVE);

        $repository = self::getMockBuilder(UrlAliasRepository::class)->disableOriginalConstructor()->getMock();
        for ($i = 0; $i <= (64 - 8); ++$i) {
            $repository->expects($this->at($i))->method('findOneByPublicUrl')->willReturn($dummy);
        }

        $generator = new class() implements ShortUrlHashGeneratorInterface {
            public function generate($url, $length)
            {
                return substr('63f9b749fe4af3401efd80241a77178902b04b71dd3caf4148712b914172ca01', 0, $length);
            }
        };
        $aliasing = self::getMockBuilder(Aliasing::class)->disableOriginalConstructor()->getMock();
        $aliasing->method('getRepository')->willReturn($repository);

        // 2. act
        $manager = new ShortUrlManager($aliasing, $generator);
        $manager->getAlias($url);
    }
}
