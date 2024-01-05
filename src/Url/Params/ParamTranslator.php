<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url\Params;

use Symfony\Contracts\Translation\TranslatorInterface;
use Zicht\Bundle\UrlBundle\Url\Params\Translator\CallbackTranslator;
use Zicht\Bundle\UrlBundle\Url\Params\Translator\CompositeTranslator;

/**
 * Maps Solr parameters to human-readably translations.
 */
class ParamTranslator extends CompositeTranslator
{
    /**
     * @param TranslatorInterface $translator
     * @param array<string, string> $mapping
     */
    public function __construct($translator, $mapping = ['keywords' => 'solr.mapping.keywords'])
    {
        $mapping = array_map(
            static function ($message) use ($translator) {
                return $translator->trans($message, [], 'template');
            },
            $mapping
        );

        foreach ($mapping as $keyName => $keyTranslation) {
            $this->add(
                new CallbackTranslator(
                    $keyName,
                    $keyTranslation,
                    // Minor hack to allow e.g. 'Jazz/Pop' to convert to 'Jazz--Pop' in the URL
                    static function ($v) {
                        return str_replace('--', '/', $v);
                    },
                    static function ($v) {
                        return str_replace('/', '--', $v);
                    }
                )
            );
        }
    }
}
