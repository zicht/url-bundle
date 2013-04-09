<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url\Params;

interface Translator {
    function translateKeyInput($keyName);
    function translateValueInput($keyName, $value);
    function translateKeyOutput($keyName);
    function translateValueOutput($keyName, $value);
}