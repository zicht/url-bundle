<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * Interface UrlMapperInterface
 *
 * @package Zicht\Bundle\UrlBundle\Aliasing\Mapper
 */
interface UrlMapperInterface
{
    /**
     * Convert internal aliases to public
     */
    const MODE_INTERNAL_TO_PUBLIC = 'internal-to-public';

    /**
     * convert public aliases to internal
     */
    const MODE_PUBLIC_TO_INTERNAL = 'public-to-internal';

    /**
     * Check if the mapper supports the given contentType
     *
     * @param string $contentType
     *
     * @return boolean
     */
    public function supports($contentType);

    /**
     * Implementation to transform the given content to proper public aliases
     *
     * @param string $content
     * @param string $mode
     * @param Aliasing $aliaser
     *
     * @return mixed
     */
    public function processAliasing($content, $mode, Aliasing $aliaser);
}