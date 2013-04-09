<?php

/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url\Params\Translator;

use Zicht\Bundle\UrlBundle\Url\Params\Translator;

class CompositeTranslator implements Translator {
    protected $translators = array();

    function translateKeyInput($keyName) {
        foreach($this->translators as $translator) {
            if(false !== ($translation = $translator->translateKeyInput($keyName))) {
                return $translation;
            }
        }
        return false;
    }

    function translateValueInput($keyName, $value) {
        foreach($this->translators as $translator) {
            if(false !== ($translation = $translator->translateValueInput($keyName, $value))) {
                return $translation;
            }
        }
        return false;
    }

    function translateKeyOutput($keyName) {
        foreach($this->translators as $translator) {
            if(false !== ($translation = $translator->translateKeyOutput($keyName))) {
                return $translation;
            }
        }
        return false;
    }

    function translateValueOutput($keyName, $value) {
        foreach($this->translators as $translator) {
            if(false !== ($translation = $translator->translateValueOutput($keyName, $value))) {
                return $translation;
            }
        }
        return false;
    }


    function add(Translator $translator) {
        $this->translators[]= $translator;
        return $this;
    }
}