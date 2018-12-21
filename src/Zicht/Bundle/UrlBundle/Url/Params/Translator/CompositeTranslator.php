<?php

/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url\Params\Translator;

use Zicht\Bundle\UrlBundle\Url\Params\Translator;

/**
 * Composite translator which delegates to the translator that translates a key.
 */
class CompositeTranslator implements Translator
{
    protected $translators = [];

    /**
     * {@inheritdoc}
     */
    public function translateKeyInput($keyName)
    {
        foreach ($this->translators as $translator) {
            if (false !== ($translation = $translator->translateKeyInput($keyName))) {
                return $translation;
            }
        }

        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function translateValueInput($keyName, $value)
    {
        foreach ($this->translators as $translator) {
            if (false !== ($translation = $translator->translateValueInput($keyName, $value))) {
                return $translation;
            }
        }

        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function translateKeyOutput($keyName)
    {
        foreach ($this->translators as $translator) {
            if (false !== ($translation = $translator->translateKeyOutput($keyName))) {
                return $translation;
            }
        }

        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function translateValueOutput($keyName, $value)
    {
        foreach ($this->translators as $translator) {
            if (false !== ($translation = $translator->translateValueOutput($keyName, $value))) {
                return $translation;
            }
        }

        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function add(Translator $translator)
    {
        $this->translators[] = $translator;

        return $this;
    }
}
