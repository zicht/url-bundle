<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Validator\Constraints;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ContainsUniquePublicUrlValidator
 *
 * @package Zicht\Bundle\UrlBundle\Validator\Constraints
 */
class ContainsUniquePublicUrlValidator extends ConstraintValidator
{
    /** @var Registry  */
    protected $doctrine;
    /** @var bool  */
    protected $isStrict;

    /**
     * Constructor
     *
     * @param Registry $doctrine
     * @param bool $isStrict
     */
    public function __construct(Registry $doctrine, $isStrict)
    {
        $this->doctrine = $doctrine;
        $this->isStrict = $isStrict;
    }


    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (false === $this->isStrict) {
            return;
        }

        if (0 < $this->getPublicUrlCount($value)) {
            $this->context->addViolation($constraint->message, ['%url%' => $value]);
        }
    }

    /**
     * @param string $url
     * @return int
     */
    protected function getPublicUrlCount($url)
    {
        return (int)$this->doctrine->getConnection()->query($this->fmtQuery($url))->fetchColumn(0);
    }

    /**
     * Create a select (count) query with the given url.
     *
     * @param string $url
     * @return string
     */
    protected function fmtQuery($url)
    {
        return sprintf('SELECT COUNT(*) FROM url_alias WHERE public_url = %s', $this->doctrine->getConnection()->quote($url));
    }
}
