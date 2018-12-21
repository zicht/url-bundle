<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Zicht\Bundle\UrlBundle\Aliasing\UrlAliasRepositoryInterface;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * Default repository implementation for url aliases
 */
class UrlAliasRepository extends EntityRepository implements UrlAliasRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByPublicUrl($publicUrl, $mode = UrlAlias::REWRITE)
    {
        $where = ['public_url' => $publicUrl];
        if (null !== $mode) {
            $where['mode'] = $mode;
        }
        return $this->findOneBy($where, ['id' => 'ASC']);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByInternalUrl($internalUrl, $mode = UrlAlias::REWRITE)
    {
        $where = ['internal_url' => $internalUrl];
        if (null !== $mode) {
            $where['mode'] = $mode;
        }
        return $this->findOneBy($where, ['id' => 'ASC']);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByInternalUrl($internalUrl)
    {
        return $this->findBy(
            ['internal_url' => $internalUrl],
            ['id' => 'ASC']
        );
    }
}
