<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Aliasing;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * Common interface for the repository containing the url aliases.
 */
interface UrlAliasRepositoryInterface
{
    public function findOneByPublicUrl($publicUrl, $mode = UrlAlias::REWRITE);
    public function findOneByInternalUrl($internalUrl, $mode = UrlAlias::REWRITE);
    public function findAllByInternalUrl($internalUrl);
}