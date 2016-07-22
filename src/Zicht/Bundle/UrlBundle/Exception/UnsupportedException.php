<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Exception;

use InvalidArgumentException;

/**
 * Thrown whenever an url is requested for an unsupported object
 */
class UnsupportedException extends InvalidArgumentException
{
}