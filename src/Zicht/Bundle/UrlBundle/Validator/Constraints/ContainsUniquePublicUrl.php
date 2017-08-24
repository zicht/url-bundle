<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class ContainsUniquePublicUrl
 *
 * @package Zicht\Bundle\UrlBundle\Validator\Constraints
 */
class ContainsUniquePublicUrl extends Constraint
{
    public $message = "Public url '%url%' already exists.";
}
