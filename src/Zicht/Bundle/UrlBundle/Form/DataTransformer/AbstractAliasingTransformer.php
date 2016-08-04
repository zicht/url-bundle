<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\UrlMapperInterface;

/**
 * Provides a Template Method pattern for implementing different mapping types
 *
 * Class AbstractAliasingTransformer
 * @package Zicht\Bundle\UrlBundle\Form\DataTransformer
 */
abstract class AbstractAliasingTransformer implements DataTransformerInterface
{
    /**
     * @var Aliasing
     */
    protected $aliasing;

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
     * Transforms a string containing internal urls to string to public urls.
     *
     * @param string $data
     * @return null|string
     */
    public function transform($data)
    {
        return $this->map($data, UrlMapperInterface::MODE_INTERNAL_TO_PUBLIC);
    }

    /**
     * Tranforms a string containing public urls to string with internal urls.
     *
     * @param string $data
     * @return null|string
     */
    public function reverseTransform($data)
    {
        return $this->map($data, UrlMapperInterface::MODE_PUBLIC_TO_INTERNAL);
    }

    /**
     * Implement the actual mapping with the specified mode.
     *
     * @param string $data
     * @param string $mode
     * @return string
     */
    public final function map($data, $mode)
    {
        if (null === $data) {
            return $data;
        }

        return $this->doMap($data, $mode);
    }

    abstract protected function doMap($data, $mode);
}