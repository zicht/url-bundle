<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Bundle\UrlBundle\Url;

/**
 * Interface for providers that handle autosuggest.
 */
interface SuggestableProvider
{
    /**
     * Suggest url's based on the passed pattern. The return value must be an array containing "label" and "value" keys.
     *
     * @param string $pattern
     * @return mixed
     */
    public function suggest($pattern);
}
