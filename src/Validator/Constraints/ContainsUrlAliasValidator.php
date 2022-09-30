<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Validator\Constraints;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

class ContainsUrlAliasValidator extends ConstraintValidator
{
    /** @var Registry */
    protected $doctrine;

    /** @var bool */
    protected $isStrict;

    /**
     * @param bool $isStrict
     */
    public function __construct(Registry $doctrine, $isStrict = false)
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
        if (!$value instanceof UrlAlias) {
            return;
        }

        if (false === $this->isStrict) {
            return;
        }

        // check for a managed state, if a empty array is returned
        // it is save to say we got a new entity.
        if ([] !== ($original = $this->getOriginalData($value))) {
            // no change in public_url field while updating, so no validation needed.
            if ($original['public_url'] === $value->getPublicUrl()) {
                return;
            }
        }

        if (0 < $this->getPublicUrlCount($value->getPublicUrl())) {
            $this->addViolation($value->getPublicUrl(), $constraint);
        }
    }

    /**
     * @param string $url
     */
    public function addViolation($url, Constraint $constraint)
    {
        $this->context->addViolation($constraint->message, ['%url%' => $url]);
    }

    /**
     * @param mixed $value
     * @return array
     */
    public function getOriginalData($value)
    {
        return $this->doctrine->getManager()->getUnitOfWork()->getOriginalEntityData($value);
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
        $em = $this->doctrine->getManager();
        $classMetaData = $em->getClassMetadata(UrlAlias::class);

        return sprintf('SELECT COUNT(*) FROM %s WHERE public_url = %s', $classMetaData->getTableName(), $this->doctrine->getConnection()->quote($url));
    }
}
