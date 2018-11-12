<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class ContainsUrlAlias
 *
 * @package Zicht\Bundle\UrlBundle\Validator\Constraints
 */
class ContainsUrlAlias extends Constraint
{
    public $message = "Public url '%url%' already exists.";

    /**
     * @{inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
