<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Controller;

use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\TestCase;

class StaticReferenceControllerTest extends TestCase
{
    public function testRedirectAction()
    {
        $controller = new \Zicht\Bundle\UrlBundle\Controller\SuggestUrlController();
        $container = new \Symfony\Component\DependencyInjection\Container();

        $provider = (new Generator())->getMock('Zicht\Bundle\UrlBundle\Url\SuggestableProvider');
        $container->set('zicht_url.provider', $provider);
        $controller->setContainer($container);

        $provider->expects($this->once())->method('suggest')->with('foo')->will(
            $this->returnValue(
                [
                    ['a' => 'b']
                ]
            )
        );
        $req = new \Symfony\Component\HttpFoundation\Request();
        $req->attributes->set('pattern', 'foo');

        $response = $controller->suggestUrlAction($req);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $response);

        $res = json_decode($response->getContent());
        $this->assertEquals('b', $res->suggestions[0]->a);
    }
}
