<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Entity\Repository;

use \Doctrine\ORM\EntityRepository;
use \Zicht\Bundle\UrlBundle\Aliasing\UrlAliasRepositoryInterface;

/**
 * Default repository implementation for url aliases
 *
 * @codeCoverageIgnore
 */
class UrlAliasRepository extends EntityRepository implements UrlAliasRepositoryInterface
{
}