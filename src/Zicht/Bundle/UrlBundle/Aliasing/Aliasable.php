<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Aliasing;

/**
 * Interface Aliasable provides an interface for customized aliases.
 */
interface Aliasable
{
    /**
     * Returns the title to be used for aliasing purposes
     *
     * @return mixed
     */
    public function getAliasTitle();
}