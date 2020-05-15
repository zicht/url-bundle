<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for the static references
 */
class StaticReferenceRepository extends EntityRepository
{
    /**
     * Returns the references for the specified locale
     *
     * @param string $locale
     * @return mixed
     */
    public function getAll($locale)
    {
        $qb = $this->createQueryBuilder('r');

        $qb->addSelect('t');
        $qb->innerJoin('r.translations', 't');
        $qb->andWhere('t.locale=:locale');
        $qb->setParameter(':locale', $locale);

        return $qb->getQuery()->execute();
    }
}
