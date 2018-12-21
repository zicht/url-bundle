<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

/**
 * Class JsonMapper
 *
 * Helper to map urls in an JSON string from internal to public aliasing or vice versa.
 *
 * @deprecated This implementation is rather optimistic: it replaces all "value" keys
 */
class JsonMapper extends AbstractMapper
{
    public function __construct()
    {
        parent::__construct(['application/json'], '/((?:"value")\s*:\s*")([^"]+)(["])/');
    }
}
