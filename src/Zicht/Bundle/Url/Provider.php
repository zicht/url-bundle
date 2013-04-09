<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;


interface Provider
{
    /**
     * Must return true if the current provider matches the object, i.e. knows how to generate a URL for the passed
     * object.
     *
     * @param $object
     * @return mixed
     */
    function supports($object);


    /**
     * Returns the URL for the object. Should throw a NotSupportedException if the passed object is not supported.
     *
     * @param $object
     * @return mixed
     */
    function url($object);
}