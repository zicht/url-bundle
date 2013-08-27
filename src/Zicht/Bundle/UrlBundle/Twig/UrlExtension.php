<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Twig;

use \Twig_Extension;
use Zicht\Bundle\UrlBundle\Exception\UnsupportedException;

class UrlExtension extends Twig_Extension
{
    /**
     * Construct the extension with the passed object as provider. The provider is typically a DelegatingProvider
     * that delegates to all registered url providers.
     *
     * @param \Zicht\Bundle\UrlBundle\Url\Provider $provider
     */
    public function __construct($provider)
    {
        $this->provider = $provider;
    }


    /**
     * Registers the twig functions provided by this extension.
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'object_url'       => new \Twig_Function_Method($this, 'object_url'),
            'static_ref'       => new \Twig_Function_Method($this, 'static_ref'),
            'static_reference' => new \Twig_Function_Method($this, 'static_ref')
        );
    }


    /**
     * Returns an url based on the passed object.
     *
     * @param object $object
     * @return string
     */
    function object_url($object, $defaultIfNotFound = null)
    {
        try {
            $ret = $this->provider->url($object);
        } catch (\Zicht\Bundle\UrlBundle\Exception\UnsupportedException $e) {
            if (null === $defaultIfNotFound) {
                throw $e;
            } else {
                if (true === $defaultIfNotFound) {
                    $ret = (string)$object;
                } else {
                    $ret = $defaultIfNotFound;
                }
            }
        }
        return $ret;
    }

    /**
     * Returns a static reference, i.e. an url that is provided based on a simple string.
     *
     * @param string $name
     * @param array  $params
     *
     * @return string
     */
    function static_ref($name, $params = null)
    {
        if (!isset($this->static_refs[$name])) {
            try {
                $this->static_refs[$name] = $this->provider->url((string) $name);
            } catch (UnsupportedException $e) {
                $this->static_refs[$name] = '/[static_reference: '. $name . ']';
            }
        }

        $ret = $this->static_refs[$name];
        if ($params) {
            $ret .= '?' . http_build_query($params, 0, '&');
        }

        return $ret;
    }
    private $static_refs = array();


    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    function getName()
    {
        return 'zicht_url';
    }
}