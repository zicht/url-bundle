<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url\Translator;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\UrlBundle\Url\Params\Translator;

class Zicht_Search_Faceted_Translator_CompositeTest extends TestCase
{
    public function testComposite()
    {
        $translator = new Translator\CompositeTranslator();

        $translator->add(
            new Translator\StaticTranslator(
                'internal_static_key',
                'readable-user-key',
                [
                    'internal_value' => 'readable-user-value',
                ]
            )
        )->add(
            new Translator\CallbackTranslator(
                'internal_callback_key',
                'readable-user-callback-key',
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
            )
        );

        $this->assertEquals('internal_static_key', $translator->translateKeyInput('readable-user-key'));
        $this->assertEquals('internal_callback_key', $translator->translateKeyInput('readable-user-callback-key'));
        $this->assertEquals(false, $translator->translateKeyInput('invalid-value'));

        $this->assertEquals('readable-user-key', $translator->translateKeyOutput('internal_static_key'));
        $this->assertEquals('readable-user-callback-key', $translator->translateKeyOutput('internal_callback_key'));
        $this->assertEquals(false, $translator->translateKeyOutput('invalid-value'));
    }
}
