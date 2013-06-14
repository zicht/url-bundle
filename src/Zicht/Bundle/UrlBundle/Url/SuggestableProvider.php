<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */
namespace Zicht\Bundle\UrlBundle\Url;

/**
 * Suggestable providers are capable of providing input for the UrlType.
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