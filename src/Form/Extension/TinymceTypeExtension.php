<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Zicht\Bundle\AdminBundle\Form\TinymceType;
use Zicht\Bundle\UrlBundle\Form\DataTransformer\HtmlTransformer;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

class TinymceTypeExtension extends AbstractTypeExtension
{
    /**
     * @var Aliasing
     */
    private $aliasing;

    /**
     * @param Aliasing $aliasing
     */
    public function __construct(Aliasing $aliasing)
    {
        $this->aliasing = $aliasing;
    }

    /** {@inheritDoc} */
    public function getExtendedTypes()
    {
        return [TinymceType::class];
    }

    /**
     * {@inheritDoc}
     * @deprecated since Symfony 4.2, use getExtendedTypes() instead.
     */
    public function getExtendedType()
    {
       return TinymceType::class;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new HtmlTransformer($this->aliasing));
    }
}
