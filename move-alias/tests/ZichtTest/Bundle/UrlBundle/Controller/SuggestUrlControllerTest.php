<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\UrlBundle\Controller;

class SuggestUrlControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testSuggestUrlAction()
    {
        $controller = new \Zicht\Bundle\UrlBundle\Controller\StaticReferenceController();
        $container = new \Symfony\Component\DependencyInjection\Container();

        $provider = $this->getMock('Zicht\Bundle\UrlBundle\Url\Provider');
        $container->set('zicht_url.provider', $provider);
        $controller->setContainer($container);

        $provider->expects($this->once())->method('url')->with('foo')->will($this->returnValue('/bar'));
        $response = $controller->redirectAction('foo');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
    }
}