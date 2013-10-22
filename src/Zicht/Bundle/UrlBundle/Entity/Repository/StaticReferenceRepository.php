<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Entity\Repository;

use \Doctrine\ORM\EntityRepository;

class StaticReferenceRepository extends EntityRepository
{
    public function getAll($locale)
    {
        return $this
            ->createQueryBuilder('r')
            ->addSelect('t')
            ->innerJoin('r.translations', 't')
            ->andWhere('t.locale=:locale')
            ->setParameter(':locale', $locale)
            ->getQuery()
            ->execute()
        ;
    }
}