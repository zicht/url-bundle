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
class ContainsUniquePublicUrlValidator  extends ConstraintValidator
{
    /** @var Registry  */
    protected $doctrine;

    /**
     * @param Registry $doctrine
     */
    function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }


    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
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
     * @param $url
     * @return string
     */
    protected function fmtQuery($url)
    {
        return sprintf('SELECT COUNT(*) FROM url_alias WHERE public_url = %s', $this->doctrine->getConnection()->quote($url));
    }
}