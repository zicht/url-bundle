<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url\Params;

/**
 * A translator can generate seo-friendly url segments for keys and values of an URI.
 *
 * For example: terms=1,12 could be translated to 'zoektermen=paarden,vissen'. The translator can translate
 * the keys both ways.
 */
interface Translator
{
    /**
     * Translate a key that comes from the client and return an internal key.
     *
     * @param string $keyName
     * @return string
     */
    public function translateKeyInput($keyName);

    /**
     * Translate a value input from the client return an internal value representation.
     *
     * @param string $keyName
     * @param string $value
     * @return string
     */
    public function translateValueInput($keyName, $value);

    /**
     * Do the inverse of translateKeyInput
     *
     * @param string $keyName
     * @return string
     */
    public function translateKeyOutput($keyName);

    /**
     * Do the inverse of translateValueInput
     *
     * @param string $keyName
     * @param string $value
     * @return string
     */
    public function translateValueOutput($keyName, $value);
}
