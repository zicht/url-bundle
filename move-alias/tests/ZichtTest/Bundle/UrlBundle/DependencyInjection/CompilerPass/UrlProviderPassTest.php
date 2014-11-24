<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\DependencyInjection;
 
class UrlProviderPassTest extends \PHPUnit_Framework_TestCase
{
    public function testPass()
    {
        $p = new \Zicht\Bundle\UrlBundle\DependencyInjection\Compiler\UrlProviderPass();
        $p->process(new \Symfony\Component\DependencyInjection\ContainerBuilder());

        //TODO
    }
}