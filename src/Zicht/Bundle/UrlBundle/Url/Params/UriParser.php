<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url\Params;

/**
 * Parser for key/value pairs in the url.
 */
class UriParser implements Translator
{
    private $seperators = [];

    /**
     * @var Translator
     */
    private $translator = null;

    /**
     * @param string $paramSeparator
     * @param string $keyValueSeparator
     * @param string $valueSeparator
     */
    public function __construct($paramSeparator = '/', $keyValueSeparator = '=', $valueSeparator = ',')
    {
        $this->seperators['param']     = $paramSeparator;
        $this->seperators['key_value'] = $keyValueSeparator;
        $this->seperators['value']     = $valueSeparator;
    }


    /**
     * Translator
     *
     * @param Translator $translator
     * @return void
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * POST keys are not translated, but do translate the values
     *
     * @param array $post
     * @return array
     */
    public function parsePost(array $post)
    {
        $ret = [];
        foreach ($post as $key => $value) {
            if ($key) {
                $external  = $this->translateKeyOutput($key);
                $ret[$key] = [];
                if (strlen($value) > 0) {
                    if ($internal = $this->translateValueInput($external, $value)) {
                        $value = $internal;
                    }
                    $ret[$key][] = $value;
                }
            }
        }

        return $ret;
    }

    /**
     * Parse the uri
     *
     * @param string $uri
     * @return array
     */
    public function parseUri($uri)
    {
        $ret = [];
        foreach (explode($this->seperators['param'], $uri) as $params) {
            if ($params) {
                @list($key, $values) = explode($this->seperators['key_value'], $params, 2);
                $external = $key;
                if ($internal = $this->translateKeyInput($key)) {
                    $key = $internal;
                }
                $ret[$key] = [];
                foreach (explode($this->seperators['value'], $values) as $value) {
                    if (strlen($value) > 0) {
                        if ($internal = $this->translateValueInput($external, $value)) {
                            $value = $internal;
                        }
                        $ret[$key][] = urldecode($value);
                    }
                }
            }
        }

        return $ret;
    }


    /**
     * Compose an URI from the passed params with the local separators
     *
     * @param Params $params
     * @return string
     */
    public function composeUri($params)
    {
        $ret   = '';
        $first = true;

        foreach ($params as $param => $values) {
            if (!$first) {
                $ret .= $this->seperators['param'];
            }
            $first    = false;
            $internal = $param;
            if ($external = $this->translateKeyOutput($param)) {
                $param = $external;
            }
            $ret .= $param . $this->seperators['key_value'];
            $firstValue = true;
            foreach ($values as $value) {
                if ($external = $this->translateValueOutput($internal, $value)) {
                    $value = $external;
                }
                if (!$firstValue) {
                    $ret .= $this->seperators['value'];
                } else {
                    $firstValue = false;
                }
                $ret .= urlencode($value);
            }
        }

        return $ret;
    }


    /**
     * Proxy method for translateKeyInput() of the translator
     *
     * @param string $keyName
     * @return bool
     */
    final public function translateKeyInput($keyName)
    {
        if ($this->translator) {
            return $this->translator->translateKeyInput($keyName);
        }

        return false;
    }


    /**
     * Proxy method for translateValueInput() of the translator
     *
     * @param string $keyName
     * @param mixed $value
     * @return bool
     */
    final public function translateValueInput($keyName, $value)
    {
        if ($this->translator) {
            return $this->translator->translateValueInput($keyName, $value);
        }

        return false;
    }


    /**
     * Proxy method for translateKeyOutput() of the translator
     *
     * @param string $keyName
     * @return bool
     */
    final public function translateKeyOutput($keyName)
    {
        if ($this->translator) {
            return $this->translator->translateKeyOutput($keyName);
        }

        return false;
    }

    /**
     * Proxy method for translateValueOutput() of the translator
     *
     * @param string $keyName
     * @param string $value
     * @return bool
     */
    final public function translateValueOutput($keyName, $value)
    {
        if ($this->translator) {
            return $this->translator->translateValueOutput($keyName, $value);
        }

        return false;
    }
}
