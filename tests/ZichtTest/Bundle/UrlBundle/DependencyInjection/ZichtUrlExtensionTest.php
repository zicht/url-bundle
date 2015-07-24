<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url\DependencyInjection;


/**
 * @property \Symfony\Component\DependencyInjection\ContainerBuilder $cb
 */
class ZichtUrlExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->extension = new \Zicht\Bundle\UrlBundle\DependencyInjection\ZichtUrlExtension();
        $this->cb = new \Symfony\Component\DependencyInjection\ContainerBuilder(
            $this->params = new \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag(array(
                'twig.form.resources' => array()
            ))
        );
    }

    public function testFormTwigResourcesGetRegistered()
    {
        $this->extension->load(array(), $this->cb);
        $this->assertContains('ZichtUrlBundle::form_theme.html.twig', $this->params->get('twig.form.resources'));
    }


    /**
     * @dataProvider conditionalServices
     */
    public function testConditionalServices($config, $expectedServices)
    {
        $this->extension->load(array($config), $this->cb);
        $this->assertContains('ZichtUrlBundle::form_theme.html.twig', $this->params->get('twig.form.resources'));

        foreach ($expectedServices as $id => $exists) {
            $this->assertEquals($exists, $this->cb->hasDefinition($id));
        }
    }


    public function conditionalServices()
    {
        return array(
            array(
                array('logging' => true),
                array('zicht_url.logging' => true)
            ),
            array(
                array('logging' => false),
                array('zicht_url.logging' => false)
            ),
            array(
                array('db_static_ref' => true),
                array('zicht_url.db_static_refs' => true)
            ),
            array(
                array('logging' => false),
                array('zicht_url.logging' => false)
            ),
            array(
                array('admin' => false),
                array('zicht_url.admin.url_alias' => false)
            ),
            array(
                array('admin' => true),
                array('zicht_url.admin.url_alias' => true)
            ),
            array(
                array('aliasing' => false),
                array('zicht_url.aliasing' => false)
            ),
            array(
                array(
                    'aliasing' => array(
                        'enabled' => true,
                        'exclude_patterns' => array('/foo/', '/bar')
                    ),
                ),
                array('zicht_url.aliasing' => true)
            ),
        );
    }

    public function testBuild()
    {
        $code = $this->cb->compile();
    }
}
