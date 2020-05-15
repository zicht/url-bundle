<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class AliasToInternalUrlTransformer
 *
 */
class HtmlTransformer extends AbstractAliasingTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function doMap($html, $mode)
    {
        return $this->aliasing->mapContent('text/html', $mode, $html, []);
    }
}
