<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Url;

/**
 * Implementing a listable provider will make the url's available on the
 * URL suggest usable by TinyMCE. See the SuggestUrlController
 */
interface ListableProvider
{
    /**
     * List all URL's
     */
    public function all();
}
