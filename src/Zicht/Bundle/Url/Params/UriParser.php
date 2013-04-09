<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url\Params;

class UriParser implements Translator {
    private $_separators = array();

    /**
     * @var Translator
     */
    private $translator = null;

    function __construct($paramSeparator = '/', $keyValueSeparator = '=', $valueSeparator = ',') {
        $this->_separators['param'] = $paramSeparator;
        $this->_separators['key_value'] = $keyValueSeparator;
        $this->_separators['value'] = $valueSeparator;
    }


    function setTranslator(Translator $translator) {
        $this->translator = $translator;
    }

    /**
     * POST keys are not translated, but do translate the values
     *
     * @param array $post
     * @return array
     */
    function parsePost(array $post) {
        $ret = array();
        foreach ($post as $key => $value) {
            if ($key) {
                $external = $this->translateKeyOutput($key);
                $ret[$key] = array();
                if (strlen($value) > 0) {
                    if($internal = $this->translateValueInput($external, $value)) {
                        $value = $internal;
                    }
                    $ret[$key][] = $value;
                }
            }
        }
        return $ret;
    }


    function parseUri($uri) {
        $ret = array();
        foreach (explode($this->_separators['param'], $uri) as $params) {
            if ($params) {
                list($key, $values) = explode($this->_separators['key_value'], $params, 2);
                $external = $key;
                if($internal = $this->translateKeyInput($key)) {
                    $key = $internal;
                }
                $ret[$key] = array();
                foreach (explode($this->_separators['value'], $values) as $value) {
                    if (strlen($value) > 0) {
                        if($internal = $this->translateValueInput($external, $value)) {
                            $value = $internal;
                        }
                        $ret[$key][] = urldecode($value);
                    }
                }
            }
        }
        return $ret;
    }


    function composeUri($params) {
        $ret = '';
        $first = true;

        foreach ($params as $param => $values) {
            if (!$first) {
                $ret .= $this->_separators['param'];
            }
            $first = false;
            $internal = $param;
            if($external = $this->translateKeyOutput($param)) {
                $param = $external;
            }
            $ret .= $param . $this->_separators['key_value'];
            $firstValue = true;
            foreach($values as $value) {
                if($external = $this->translateValueOutput($internal, $value)) {
                    $value = $external;
                }
                if(!$firstValue) {
                    $ret .= $this->_separators['value'];
                } else {
                    $firstValue = false;
                }
                $ret .= urlencode($value);
            }
        }
        return $ret;
    }


    final function translateKeyInput($keyName) {
        if($this->translator) {
            return $this->translator->translateKeyInput($keyName);
        }
        return false;
    }


    final function translateValueInput($keyName, $value) {
        if($this->translator) {
            return $this->translator->translateValueInput($keyName, $value);
        }
        return false;
    }


    final function translateKeyOutput($keyName) {
        if($this->translator) {
            return $this->translator->translateKeyOutput($keyName);
        }
        return false;
    }


    final function translateValueOutput($keyName, $value) {
        if($this->translator) {
            return $this->translator->translateValueOutput($keyName, $value);
        }
        return false;
    }
}