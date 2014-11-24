<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\DependencyInjection;
 
class ReplaceUrlProviderServicePassTest extends \PHPUnit_Framework_TestCase
{
    public function testPass()
    {
        $p = new \Zicht\Bundle\UrlBundle\DependencyInjection\Compiler\ReplaceUrlProviderServicePass();
        $p->process(new \Symfony\Component\DependencyInjection\ContainerBuilder());

        //TODO
    }

}