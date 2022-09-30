<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url\DependencyInjection;

use PHPUnit\Framework\TestCase;

/**
 * @property \Symfony\Component\DependencyInjection\ContainerBuilder $cb
 */
class ZichtUrlExtensionTest extends TestCase
{
    private $extension;

    private $cbs;

    public function testFormTwigResourcesGetRegistered()
    {
        $this->extension->load([], $this->cb);
        $this->assertContains('@ZichtUrl/form_theme.html.twig', $this->params->get('twig.form.resources'));
    }

    /**
     * @dataProvider conditionalServices
     * @param mixed $config
     * @param mixed $expectedServices
     */
    public function testConditionalServices($config, $expectedServices)
    {
        $this->extension->load([$config], $this->cb);

        $this->assertContains('@ZichtUrl/form_theme.html.twig', $this->params->get('twig.form.resources'));

        foreach ($expectedServices as $id => $exists) {
            $this->assertEquals($exists, $this->cb->hasDefinition($id));
        }
    }

    public function conditionalServices()
    {
        return [
            [
                ['logging' => true],
                ['zicht_url.logging' => true],
            ],
            [
                ['logging' => false],
                ['zicht_url.logging' => false],
            ],
            [
                ['db_static_ref' => true],
                ['zicht_url.db_static_refs' => true],
            ],
            [
                ['logging' => false],
                ['zicht_url.logging' => false],
            ],
            [
                ['admin' => false],
                ['zicht_url.admin.url_alias' => false],
            ],
            [
                ['admin' => true],
                ['zicht_url.admin.url_alias' => true],
            ],
            [
                ['aliasing' => false],
                ['zicht_url.aliasing' => false],
            ],
            [
                [
                    'aliasing' => [
                        'enabled' => true,
                        'exclude_patterns' => ['/foo/', '/bar'],
                    ],
                ],
                ['zicht_url.aliasing' => true],
            ],
        ];
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testBuild()
    {
        $code = $this->cb->compile();
    }

    protected function setUp(): void
    {
        $this->extension = new \Zicht\Bundle\UrlBundle\DependencyInjection\ZichtUrlExtension();
        $this->cb = new \Symfony\Component\DependencyInjection\ContainerBuilder(
            $this->params = new \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag(
                [
                    'twig.form.resources' => [],
                ]
            )
        );
    }
}
