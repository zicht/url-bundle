<?php
/**
 * @author Muhammed Akbulut <muhammed@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Zicht\Bundle\UrlBundle\Service\UrlValidator;

/**
 * Class ContainsValidUrlsValidator
 *
 * @package Zicht\Bundle\UrlBundle\Validator\Constraints
 */
class ContainsValidUrlsValidator extends ConstraintValidator
{

    /**
     * @var UrlValidator
     */
    private $urlValidator;

    /**
     * ContainsValidUrlsValidator constructor.
     *
     * @param UrlValidator $urlValidator
     */
    public function __construct(UrlValidator $urlValidator)
    {
        $this->urlValidator = $urlValidator;
    }

    /**
     * Checks if the passed value is valid.
     * Validates only urls within a href's
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        // collect urls
        $matches = array();

        // matches all urls withing a href's
        preg_match_all("#<a\s+(?:[^>]*?\s+)?href=\"((https*:)*//[^\"]*)\">.*</a>#U", $value, $matches);

        if (count($matches) === 0 || !isset($matches[1])) {
            return;
        }

        $externalUrls = $matches[1];

        // validate urls
        foreach ($externalUrls as $externalUrl) {
            if ($this->urlValidator->validate($externalUrl) === false) {
                $this->context
                    ->addViolation($constraint->message, ['%string%' => $externalUrl]);
            }
        }
    }
}
