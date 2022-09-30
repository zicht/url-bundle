<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Form\DataTransformer;

/**
 * Class AliasToInternalUrlTransformer
 */
class HtmlTransformer extends AbstractAliasingTransformer
{
    protected function doMap($html, $mode)
    {
        return $this->aliasing->mapContent('text/html', $mode, $html, []);
    }
}
