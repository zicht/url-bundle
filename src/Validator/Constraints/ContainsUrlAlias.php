<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ContainsUrlAlias extends Constraint
{
    /** @var string */
    public $message = "Public url '%url%' already exists.";

    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
