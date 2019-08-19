<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zicht\Bundle\UrlBundle\Aliasing\UrlAliasRepositoryInterface;
use Zicht\Bundle\UrlBundle\Entity\Site;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * Default repository implementation for url aliases
 */
class UrlAliasRepository extends EntityRepository implements UrlAliasRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByPublicUrl($publicUrl, Site $site = null, $mode = UrlAlias::REWRITE)
    {
        $where = ['public_url' => $publicUrl];
        if (null !== $mode) {
            $where['mode'] = $mode;
        }
        if (null !== $site) {
            $where['site'] = $site;
        }
        return $this->findOneBy($where, ['id' => 'ASC']);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByInternalUrl($internalUrl, Site $site = null, $mode = UrlAlias::REWRITE)
    {
        $where = ['internal_url' => $internalUrl];
        if (null !== $mode) {
            $where['mode'] = $mode;
        }
        if (null !== $site) {
            $where['site'] = $site;
        }
        return $this->findOneBy($where, ['id' => 'ASC']);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByInternalUrl($internalUrl, Site $site = null)
    {
        $where = ['internal_url' => $internalUrl];
        if (null !== $site) {
            $where['site'] = $site;
        }
        return $this->findBy(
            $where,
            ['id' => 'ASC']
        );
    }
}
