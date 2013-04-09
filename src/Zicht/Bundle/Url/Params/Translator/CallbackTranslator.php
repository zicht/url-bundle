<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url\Params\Translator;

class CallbackTranslator extends StaticTranslator {
    function __construct($keyName, $keyTranslation, $valueInputTranslator, $valueOutputTranslator) {
        parent::__construct($keyName, $keyTranslation, array());
        $this->valueInputTranslator = $valueInputTranslator;
        $this->valueOutputTranslator = $valueOutputTranslator;
    }


    function translateValueInput($keyName, $value) {
        if($this->translateKeyInput($keyName) !== false) {
            return call_user_func($this->valueInputTranslator, $value);
        }
        return false;
    }

    function translateValueOutput($keyName, $value) {
        if($this->translateKeyOutput($keyName) !== false) {
            return call_user_func($this->valueOutputTranslator, $value);
        }
        return false;
    }
}