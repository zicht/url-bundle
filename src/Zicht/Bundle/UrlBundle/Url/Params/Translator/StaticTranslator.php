<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url\Params\Translator;

use Zicht\Bundle\UrlBundle\Url\Params\Translator;

/**
 * Static translator which holds mappings for keys and values.
 */
class StaticTranslator implements Translator
{
    protected $keyName;
    protected $keyTranslation;
    protected $valueTranslations;

    /**
     * @param string $keyName
     * @param string $keyTranslation
     * @param array $valueTranslations
     */
    public function __construct($keyName, $keyTranslation, $valueTranslations = [])
    {
        $this->keyName           = $keyName;
        $this->keyTranslation    = $keyTranslation;
        $this->valueTranslations = $valueTranslations;
    }


    /**
     * {@inheritdoc}
     */
    public function translateKeyInput($keyTranslation)
    {
        if ($keyTranslation == $this->keyTranslation) {
            return $this->keyName;
        }

        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function translateValueInput($keyTranslation, $valueTranslation)
    {
        if ($keyTranslation == $this->keyTranslation) {
            if (false !== ($value = array_search($valueTranslation, $this->valueTranslations))) {
                return $value;
            }
        }

        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function translateKeyOutput($keyName)
    {
        if ($keyName == $this->keyName) {
            return $this->keyTranslation;
        }

        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function translateValueOutput($keyName, $value)
    {
        if ($keyName == $this->keyName) {
            if (isset($this->valueTranslations[$value])) {
                return $this->valueTranslations[$value];
            }
        }

        return false;
    }


    /**
     * Adds a value translation
     *
     * @param string $in
     * @param string $out
     * @return self
     */
    public function addTranslation($in, $out)
    {
        $this->valueTranslations[$in] = $out;
        return $this;
    }
}
