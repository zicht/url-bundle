<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ContainsValidUrls extends Constraint
{
    /** @var string */
    public $message = 'The url "%string%" is a broken url, please check the url for validity.';

    /**
     * Returns validator name
     *
     * @return string
     */
    public function validatedBy()
    {
        return get_class($this) . 'Validator';
    }
}
