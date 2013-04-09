<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url\Params\Translator;

use Zicht\Bundle\UrlBundle\Url\Params\Translator;

class StaticTranslator implements Translator {
    function __construct($keyName, $keyTranslation, $valueTranslations = array()) {
        $this->keyName = $keyName;
        $this->keyTranslation = $keyTranslation;
        $this->valueTranslations = $valueTranslations;
    }


    function translateKeyInput($keyTranslation) {
        if($keyTranslation == $this->keyTranslation) {
            return $this->keyName;
        }
        return false;
    }


    function translateValueInput($keyTranslation, $valueTranslation) {
        if($keyTranslation == $this->keyTranslation) {
            if(false !== ($value = array_search($valueTranslation, $this->valueTranslations))) {
                return $value;
            }
        }
        return false;
    }


    function translateKeyOutput($keyName) {
        if($keyName == $this->keyName) {
            return $this->keyTranslation;
        }
        return false;
    }


    function translateValueOutput($keyName, $value) {
        if($keyName == $this->keyName) {
            if(isset($this->valueTranslations[$value])) {
                return $this->valueTranslations[$value];
            }
        }
        return false;
    }


    function addTranslation($in, $out) {
        $this->valueTranslations[$in] = $out;
    }
}