<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */


namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

use Zicht\Bundle\UrlBundle\Url\Rewriter;

/**
 * Base class for simple match mapping
 */
abstract class AbstractMapper implements UrlMapperInterface
{
    /**
     * @var array|\string[]
     */
    protected $contentTypes;

    /**
     * @var string
     */
    protected $pattern;

    /**
     * Constructor
     *
     * @param string[] $contentTypes
     * @param string $pattern
     */
    public function __construct(array $contentTypes, $pattern)
    {
        $this->contentTypes = $contentTypes;
        $this->pattern = $pattern;
    }

    /**
     * Check if the mapper supports the given contentType
     *
     * @param string $contentType
     *
     * @return boolean
     */
    public function supports($contentType)
    {
        return in_array($contentType, $this->contentTypes);
    }

    /**
     * @{inheritDoc}
     */
    public function processAliasing($content, $mode, Rewriter $rewriter)
    {
        if (!preg_match_all($this->pattern, $content, $matches, PREG_SET_ORDER)) {
            // early return: if there are no matches, no need for the rest of the processing.
            return $content;
        }
        $groups = [];
        foreach ($matches as $match) {
            $groups[$match[2]][]= $match;
        }

        return $rewriter->rewriteMatches($content, $mode, $groups);
    }
}
