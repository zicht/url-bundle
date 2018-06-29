<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Form\DataTransformer\TextTransformer;

/**
 * Type for choosing an URL
 */
class UrlType extends AbstractType
{
    /**
     * @var Aliasing
     */
    private $aliasing;

    /**
     * UrlType constructor.
     *
     * @param Aliasing $aliasing
     */
    public function __construct(Aliasing $aliasing)
    {
        $this->aliasing = $aliasing;
    }

    /**
     * @{inheritDoc}
     */
    public function getName()
    {
        return 'zicht_url';
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return 'zicht_url';
    }

    /**
     * @{inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults(
                array(
                    'with_edit_button'      => true,
                    'no_transform_public'   => false,
                    'no_transform_internal' => false,
                    'url_suggest'           => '/admin/url/suggest',
                )
            );
    }

    /**
     * @{inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);
        $view->vars['url_suggest'] = $options['url_suggest'];
        $view->vars['with_edit_button'] = $options['with_edit_button'];
    }

    /**
     * @{inheritDoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $mode = TextTransformer::MODE_TO_INTERNAL|TextTransformer::MODE_TO_PUBLIC;

        if ($options['no_transform_public']) {
            $mode ^= TextTransformer::MODE_TO_PUBLIC;
        }

        if ($options['no_transform_internal']) {
            $mode ^= TextTransformer::MODE_TO_INTERNAL;
        }

        if ($mode > 0) {
            $builder->addModelTransformer(new TextTransformer($this->aliasing, $mode));
        }
    }
}
