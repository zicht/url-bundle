<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zicht\Bundle\UrlBundle\Aliasing\UrlAliasRepositoryInterface;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * Default repository implementation for url aliases
 *
 * @codeCoverageIgnore
 */
class UrlAliasRepository extends EntityRepository implements UrlAliasRepositoryInterface
{
    public function findOneByPublicUrl($publicUrl, $mode = UrlAlias::REWRITE)
    {
        $where = ['public_url' => $publicUrl];
        if (null !== $mode) {
            $where['mode']= $mode;
        }
        return $this->findOneBy($where);
    }


    public function findOneByInternalUrl($internalUrl, $mode = UrlAlias::REWRITE)
    {
        $where = ['internal_url' => $internalUrl];
        if (null !== $mode) {
            $where['mode']= $mode;
        }
        return $this->findOneBy($where);
    }


    /**
     *
     * @param $internalUrl
     * @return UrlAlias[]
     */
    public function findAllByInternalUrl($internalUrl)
    {
        return $this->findBy(
            ['internal_url' => $internalUrl],
            ['id' => 'ASC']
        );
    }
}