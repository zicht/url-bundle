<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Aliasing;

/**
 * Common interface for the repository containing the url aliases.
 */
interface UrlAliasRepositoryInterface
{
    /**
     * Required for all repositories that contain url aliases
     *
     * @param array $criteria
     * @param array $orderBy
     * @return \Zicht\Bundle\UrlBundle\Entity\UrlAlias
     */
    public function findOneBy(array $criteria, array $orderBy = null);


    /**
     * Returns all UrlAlias instances
     *
     * @return \Zicht\Bundle\UrlBundle\Entity\UrlAlias[]
     */
    public function findAll();
}