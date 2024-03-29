<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Aliasing;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasable;
use Zicht\Bundle\UrlBundle\Aliasing\DefaultAliasingStrategy;

class Foo
{
    public function __toString()
    {
        return 'Bar';
    }
}

class Foo2 extends Foo
{
    public function getTitle()
    {
        return '~~~b a z~~~';
    }
}

class Foo3 extends Foo implements Aliasable
{
    public function getAliasTitle()
    {
        return strrev((string)$this);
    }
}

class Unaliasable
{
    public function __toString()
    {
        return '';
    }
}

class DefaultAliasingStrategyTest extends TestCase
{
    /**
     * @dataProvider cases
     * @param mixed $expect
     * @param mixed $in
     */
    public function testAliasing($expect, $in)
    {
        $strategy = new DefaultAliasingStrategy();
        $this->assertEquals($expect, $strategy->generatePublicAlias($in));
    }

    public function cases()
    {
        return [
            ['/bar', new Foo()],
            ['/b-a-z', new Foo2()],
            ['/rab', new Foo3()],
            ['/foo', 'foo'],
            [null, new Unaliasable()],
        ];
    }

    public function testUnsupported()
    {
        $this->expectException('\InvalidArgumentException');
        $strategy = new DefaultAliasingStrategy();
        $strategy->generatePublicAlias(false);
    }
}
