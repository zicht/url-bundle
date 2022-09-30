<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Form\DataTransformer;

class TextTransformer extends AbstractAliasingTransformer
{
    /**
     * Delegates the text mapping to the aliasing service.
     *
     * @param string $text
     * @param string $mode
     * @return mixed|null
     */
    protected function doMap($text, $mode)
    {
        $map = $this->aliasing->getAliasingMap([$text], $mode);

        if (count($map) === 1) {
            return current($map);
        }

        return $text;
    }
}
