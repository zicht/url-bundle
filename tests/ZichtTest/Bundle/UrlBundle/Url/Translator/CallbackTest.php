<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url\Translator;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Url\Params\Translator\CallbackTranslator;

class Zicht_Search_Faceted_Translator_CallbackTest extends TestCase
{
    function testTranslation()
    {
        $translator = new CallbackTranslator(
            'internal_key',
            'readable-user-key',
            function ($s) {
                return "prefix:{$s}:suffix";
            },
            function ($s) {
                if (substr($s, 0, 7) == 'prefix:' && substr($s, -7) == ':suffix') {
                    return substr($s, 7, -7);
                } else {
                    return false;
                }
            }
        );

        $this->assertEquals('internal_key', $translator->translateKeyInput('readable-user-key'));
        $this->assertEquals('prefix:readable-user-value:suffix', $translator->translateValueInput('readable-user-key', 'readable-user-value'));
        $this->assertEquals('readable-user-key', $translator->translateKeyOutput('internal_key'));
        $this->assertEquals('readable-user-value', $translator->translateValueOutput('internal_key', 'prefix:readable-user-value:suffix'));
    }
}
