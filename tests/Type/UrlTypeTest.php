<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Type;

use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

/**
 * @property \Zicht\Bundle\UrlBundle\Type\UrlType $type
 */
class UrlTypeTest extends TestCase
{
    public function setUp()
    {
        $aliasing = $this->getMockBuilder(Aliasing::class)->disableOriginalConstructor()->getMock();
        $this->type = new \Zicht\Bundle\UrlBundle\Type\UrlType($aliasing);
    }

    public function testGetBlockPrefix()
    {
        $this->assertEquals('zicht_url', $this->type->getBlockPrefix());
    }

    public function testGetParent()
    {
        $this->assertEquals(TextType::class, $this->type->getParent());
    }

    /**
     * @throws \ReflectionException
     * @doesNotPerformAssertions
     */
    public function testOptions()
    {
        $optionsResolver = (new Generator())->getMock('Symfony\Component\OptionsResolver\OptionsResolver', ['setDefaults']);
        $optionsResolver->expects($this->once())->method('setDefaults')->with(
            [
                'with_edit_button' => true,
                'no_transform_public' => false,
                'no_transform_internal' => false,
                'url_suggest' => '/admin/url/suggest',

            ]
        );

        $this->type->configureOptions($optionsResolver);
    }

    public function testFinishView()
    {
        $view = (new Generator())->getMock('Symfony\Component\Form\FormView');
        $view->vars['value'] = 'foo';
        $this->type->finishView(
            $view,
            (new Generator())->getMock('Symfony\Component\Form\Form', [], [], '', false),
            [
                'with_edit_button' => true,
                'url_suggest' => '/admin/url/suggest',
            ]
        );

        $this->assertArrayHasKey('url_suggest', $view->vars);
//        $this->assertArrayHasKey('current_url_title', $view->vars);
        $this->assertArrayHasKey('with_edit_button', $view->vars);
    }
}
