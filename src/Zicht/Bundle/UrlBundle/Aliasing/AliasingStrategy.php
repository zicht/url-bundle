<?php

/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Aliasing;

/**
 * Interface for providing aliases for an arbitrary object
 */
interface AliasingStrategy
{
    /**
     * Generate a public alias for the passed object
     *
     * @param mixed $subject
     * @param string $currentAlias
     * @return null|string
     */
    public function generatePublicAlias($subject, $currentAlias = '');
}
