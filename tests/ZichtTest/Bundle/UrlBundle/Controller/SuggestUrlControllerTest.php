<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Controller;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Zicht\Bundle\UrlBundle\Controller\StaticReferenceController;

/**
 * Class SuggestUrlControllerTest
 *
 * @package ZichtTest\Bundle\UrlBundle\Controller
 */
class SuggestUrlControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test
     */
    public function testSuggestUrlAction()
    {
        $controller = new StaticReferenceController();
        $container = new Container();

        $provider = $this->getMock('Zicht\Bundle\UrlBundle\Url\Provider');
        $container->set('zicht_url.provider', $provider);
        $controller->setContainer($container);

        $provider->expects($this->once())->method('url')->with('foo')->will($this->returnValue('/bar'));
        $response = $controller->redirectAction(new Request(), 'foo');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
    }
}
