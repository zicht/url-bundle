<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\UrlBundle\Type;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

/**
 * @property \Zicht\Bundle\UrlBundle\Type\UrlType $type
 */
class UrlTypeTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $aliasing = $this->getMockBuilder(Aliasing::class)->disableOriginalConstructor()->getMock();
        $this->type = new \Zicht\Bundle\UrlBundle\Type\UrlType($aliasing);
    }

    public function testGetName()
    {
        $this->assertEquals('zicht_url', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('text', $this->type->getParent());
    }

    public function testOptions()
    {
        $optionsResolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolver', array('setDefaults'));
        $optionsResolver->expects($this->once())->method('setDefaults')->with(array(
            'with_edit_button' => true,
            'no_transform_public' => false,
            'no_transform_internal' => false,
            'url_suggest' => '/admin/url/suggest',

        ));

        $this->type->setDefaultOptions($optionsResolver);
    }

    public function testFinishView()
    {
        $view = $this->getMock('Symfony\Component\Form\FormView');
        $view->vars['value'] = 'foo';
        $this->type->finishView(
            $view,
            $this->getMock('Symfony\Component\Form\Form', array(), array(), '', false),
            array(
                'with_edit_button' => true,
                'url_suggest' =>  '/admin/url/suggest',
            )
        );

        $this->assertArrayHasKey('url_suggest', $view->vars);
//        $this->assertArrayHasKey('current_url_title', $view->vars);
        $this->assertArrayHasKey('with_edit_button', $view->vars);
    }
}