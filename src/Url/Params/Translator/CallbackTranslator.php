<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url\Params\Translator;

/**
 * Translator which uses callbacks for input/output translation
 */
class CallbackTranslator extends StaticTranslator
{
    protected $valueInputTranslator;
    protected $valueOutputTranslator;

    /**
     * @param string $keyName
     * @param string $keyTranslation
     * @param callable $valueInputTranslator
     * @param callable $valueOutputTranslator
     */
    public function __construct($keyName, $keyTranslation, $valueInputTranslator, $valueOutputTranslator)
    {
        parent::__construct($keyName, $keyTranslation, []);
        $this->valueInputTranslator  = $valueInputTranslator;
        $this->valueOutputTranslator = $valueOutputTranslator;
    }

    /**
     * {@inheritdoc}
     */
    public function translateValueInput($keyName, $value)
    {
        if ($this->translateKeyInput($keyName) !== false) {
            return call_user_func($this->valueInputTranslator, $value);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function translateValueOutput($keyName, $value)
    {
        if ($this->translateKeyOutput($keyName) !== false) {
            return call_user_func($this->valueOutputTranslator, $value);
        }

        return false;
    }
}
