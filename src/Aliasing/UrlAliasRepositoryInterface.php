<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Aliasing;

use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * Common interface for the repository containing the url aliases.
 */
interface UrlAliasRepositoryInterface
{
    /**
     * Find one object by public url
     *
     * @param string $publicUrl
     * @param int $mode
     * @return mixed
     */
    public function findOneByPublicUrl($publicUrl, $mode = UrlAlias::REWRITE);

    /**
     * Find one object by internal url
     *
     * @param string $internalUrl
     * @param int $mode
     * @return mixed
     */
    public function findOneByInternalUrl($internalUrl, $mode = UrlAlias::REWRITE);

    /**
     * Find objects by internal url
     *
     * @param string $internalUrl
     * @return mixed
     */
    public function findAllByInternalUrl($internalUrl);
}
