<?php
/**
 * @author Muhammed Akbulut <muhammed@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Zicht\Bundle\AdminBundle\Form\DataTransformer\AliasToInternalUrlTransformer;
use Zicht\Bundle\AdminBundle\Form\TinymceType;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

/**
 * Class TinymceTypeExtension
 *
 * @package Zicht\Bundle\UrlBundle\Form\Extension
 */
class TinymceTypeExtension extends AbstractTypeExtension
{
    /**
     * @var Aliasing
     */
    private $aliasing;

    /**
     * TinymceTypeExtension constructor.
     *
     * @param Aliasing $aliasing
     */
    public function __construct(Aliasing $aliasing)
    {
        $this->aliasing = $aliasing;
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
       return TinymceTypeExtension::class;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var FormBuilderInterface $item */
        foreach ($builder->all() as $item) {
            if ($item->getType() === TinymceType::class) {
                $item->addModelTransformer(new AliasToInternalUrlTransformer($this->aliasing));
            }
        }
    }
}
