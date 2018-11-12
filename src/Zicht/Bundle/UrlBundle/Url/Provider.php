<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

/**
 * Interface for services that can provide URL's for specific objects
 */
interface Provider
{
    /**
     * Must return true if the current provider matches the object, i.e. knows how to generate a URL for the passed
     * object.
     *
     * @param mixed $object
     * @return mixed
     */
    public function supports($object);


    /**
     * Returns the URL for the object. Should throw a UnsupportedException if the passed object is not supported.
     *
     * @param mixed $object
     * @param array $options
     * @return mixed
     */
    public function url($object, array $options = []);
}
