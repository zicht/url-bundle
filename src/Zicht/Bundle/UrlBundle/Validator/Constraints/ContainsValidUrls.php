<?php
/**
 * @author Muhammed Akbulut <muhammed@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class ContainsValidUrls
 *
 * @package Zicht\Bundle\UrlBundle\Validator\Constraints
 */
class ContainsValidUrls extends Constraint
{
    public $message = 'The url "%string%" is a broken url, please check the url for validity.';

    /**
     * Returns validator name
     *
     * @return string
     */
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
}
