<?php
/**
 * @author Muhammed Akbulut <muhammed@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\UrlMapperInterface;

/**
 * Class ExternalUrlToInternalUrlTransformer
 *
 * @package Zicht\Bundle\UrlBundle\Form\DataTransformer
 */
class ExternalUrlToInternalUrlTransformer implements DataTransformerInterface
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
     * @param string $text
     * @return null|string
     */
    public function transform($text)
    {
        if ($text === null) {
            return null;
        }

        $map = $this->aliasing->getAliasingMap([$text], UrlMapperInterface::MODE_PUBLIC_TO_INTERNAL);

        if (count($map) === 1) {
            return current($map);
        }
        
        return $text;
    }

    /**
     * Tranforms a string containing internal urls to string with internal urls.
     *
     * @param string $text
     * @return null|string
     */
    public function reverseTransform($text)
    {
        if ($text === null) {
            return null;
        }

        $map = $this->aliasing->getAliasingMap([$text], UrlMapperInterface::MODE_INTERNAL_TO_PUBLIC);

        if (count($map) === 1) {
            return current($map);
        }

        return $text;
    }
}
