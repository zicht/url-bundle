<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\UrlMapperInterface;

/**
 * Provides a Template Method pattern for implementing different mapping types
 */
abstract class AbstractAliasingTransformer implements DataTransformerInterface
{
    /** Mode bit for transform method */
    const MODE_TO_PUBLIC = 1;
    /** Mode bit for reverseTransform method */
    const MODE_TO_INTERNAL = 2;

    /** @var Aliasing */
    protected $aliasing;

    /** @var int */
    protected $mode;

    /**
     * AliasToInternalUrlTransformer constructor.
     *
     * @param int $mode
     */
    public function __construct(Aliasing $aliasing, $mode = self::MODE_TO_PUBLIC | self::MODE_TO_INTERNAL)
    {
        $this->aliasing = $aliasing;
        $this->mode = $mode;
    }

    /**
     * Transforms a string containing internal urls to string to public urls.
     *
     * @param string $data
     * @return string|null
     */
    public function transform($data)
    {
        if (self::MODE_TO_PUBLIC === (self::MODE_TO_PUBLIC & $this->mode)) {
            return $this->map($data, UrlMapperInterface::MODE_INTERNAL_TO_PUBLIC);
        } else {
            return $data;
        }
    }

    /**
     * Tranforms a string containing public urls to string with internal urls.
     *
     * @param string $data
     * @return string|null
     */
    public function reverseTransform($data)
    {
        if (self::MODE_TO_INTERNAL === (self::MODE_TO_INTERNAL & $this->mode)) {
            return $this->map($data, UrlMapperInterface::MODE_PUBLIC_TO_INTERNAL);
        } else {
            return $data;
        }
    }

    /**
     * Wraps the doMap to defend for the 'null'-value case
     *
     * @param string $data
     * @param string $mode
     * @return string
     */
    final public function map($data, $mode)
    {
        if (null === $data) {
            return $data;
        }

        return $this->doMap($data, $mode);
    }

    /**
     * Implement the actual mapping with the specified mode.
     *
     * @param string $data
     * @param string $mode
     * @return string
     */
    abstract protected function doMap($data, $mode);
}
