<?php
/**
 * @author Muhammed Akbulut <muhammed@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\AdminBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\UrlMapperInterface;

/**
 * Class AliasToInternalUrlTransformer
 *
 * @package Zicht\Bundle\AdminBundle\Form\DataTransformer
 */
class AliasToInternalUrlTransformer implements DataTransformerInterface
{
    /**
     * @var Aliasing
     */
    private $aliasing;

    /**
     * AliasToInternalUrlTransformer constructor.
     *
     * @param Aliasing $aliasing
     */
    public function __construct(Aliasing $aliasing)
    {
        $this->aliasing = $aliasing;
    }

    /**
     * Transforms a string containing external urls to string with internal urls.
     *
     * @param string $html
     * @return null|string
     */
    public function transform($html)
    {
        if ($html === null) {
            return null;
        }
        
        return $this->aliasing->mapContent('html', UrlMapperInterface::MODE_PUBLIC_TO_INTERNAL, $html);
    }

    /**
     * Tranforms a string containing internal urls to string with internal urls.
     *
     * @param string $html
     * @return null|string
     */
    public function reverseTransform($html)
    {
        if ($html === null) {
            return null;
        }

        return $this->aliasing->mapContent('html', UrlMapperInterface::MODE_INTERNAL_TO_PUBLIC, $html);
    }
}
