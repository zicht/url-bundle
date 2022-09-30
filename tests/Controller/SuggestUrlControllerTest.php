<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Controller;

use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Zicht\Bundle\UrlBundle\Controller\StaticReferenceController;

class SuggestUrlControllerTest extends TestCase
{
    public function testSuggestUrlAction()
    {
        $controller = new StaticReferenceController();
        $container = new Container();

        $provider = (new Generator())->getMock('Zicht\Bundle\UrlBundle\Url\Provider');
        $container->set('zicht_url.provider', $provider);
        $controller->setContainer($container);

        $provider->expects($this->once())->method('url')->with('foo')->will($this->returnValue('/bar'));
        $response = $controller->redirectAction(new Request(), 'foo');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
    }
}
