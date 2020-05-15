<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\UrlBundle\Url\Translator;

use PHPUnit\Framework\TestCase;

class Zicht_Search_Faceted_Translator_StaticTest extends TestCase
{
    function testTranslation()
    {
        $translator = new \Zicht\Bundle\UrlBundle\Url\Params\Translator\StaticTranslator(
            'internal_key',
            'readable-user-key',
            [
                'internal_value' => 'readable-user-value'
            ]
        );
        $translator->addTranslation('internal_value2', 'readable-user-value2');

        $this->assertEquals('internal_key', $translator->translateKeyInput('readable-user-key'));
        $this->assertEquals('internal_value', $translator->translateValueInput('readable-user-key', 'readable-user-value'));
        $this->assertEquals('internal_value2', $translator->translateValueInput('readable-user-key', 'readable-user-value2'));

        $this->assertEquals('readable-user-key', $translator->translateKeyOutput('internal_key'));
        $this->assertEquals('readable-user-value', $translator->translateValueOutput('internal_key', 'internal_value'));

        $this->assertFalse($translator->translateKeyInput('readable-user-key-invalid'));
        $this->assertFalse($translator->translateValueInput('readable-user-key-invalid', 'readable-user-value'));
        $this->assertFalse($translator->translateValueInput('readable-user-key', 'readable-user-value-invalid'));

        $this->assertFalse($translator->translateKeyOutput('internal_key_invalid'));
        $this->assertFalse($translator->translateValueOutput('internal_key_invalid', 'internal_value'));
        $this->assertFalse($translator->translateValueOutput('internal_key', 'internal_value_invalid'));
    }
}
