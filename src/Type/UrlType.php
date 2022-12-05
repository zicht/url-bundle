<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zicht\Bundle\AdminBundle\Form\AutocompleteType;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

class UrlType extends AbstractType
{
    /** @var Aliasing */
    private $aliasing;

    public function __construct(Aliasing $aliasing)
    {
        $this->aliasing = $aliasing;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults(
                [
                    'repo' => 'url_alias',
                    'transformer' => 'noop', // disable all transformation. we do this ourselves with UrlTypeAutocompleteDataTransformer
                ]
            );
    }

    public function getParent()
    {
        return AutocompleteType::class;
    }

    public function getBlockPrefix()
    {
        return 'zicht_url';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new UrlTypeAutocompleteDataTransformer($this->aliasing));
    }
}
