<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

/**
 * Class JsonMapper
 *
 * Helper to map urls in an JSON string from internal to public aliasing or vice versa.
 *
 * @package Zicht\Bundle\UrlBundle\Aliasing
 * @deprecated This implementation is rather optimistic: it replaces all "value" keys
 */
class JsonMapper extends AbstractMapper
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(['application/json'], '/((?:"value")\s*:\s*")([^"]+)(["])/');
    }
}
