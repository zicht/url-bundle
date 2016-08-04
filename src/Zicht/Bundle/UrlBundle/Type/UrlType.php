<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
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
     * @{inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        $resolver->setDefaults(
            array(
            'with_edit_button' => true
            )
        );
    }

    /**
     * @{inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        // TODO make the route name an option:
        $view->vars['url_suggest'] = '/admin/url/suggest';

        $view->vars['with_edit_button'] = $options['with_edit_button'];
    }

    /**
     * @{inheritDoc}
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new TextTransformer($this->aliasing));
    }
}
