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
 * Class TextTransformer
 *
 * @package Zicht\Bundle\UrlBundle\Form\DataTransformer
 */
class TextTransformer extends AbstractAliasingTransformer
{
    /**
     * Delegates the text mapping to the aliasing service.
     *
     * @param $text
     * @param $mode
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
