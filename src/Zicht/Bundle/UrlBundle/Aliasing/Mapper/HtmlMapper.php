<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Url\Rewriter;

/**
 * Class HtmlMapper
 *
 * Helper to map urls in an HTML string from internal to public aliasing or vice versa.
 *
 * @package Zicht\Bundle\UrlBundle\Aliasing
 */
class HtmlMapper implements UrlMapperInterface
{
    use Traits\MatchingGroupReplaceTrait;

    public function __construct()
    {
        $this->htmlAttributes = [
            'a' => ['href', 'data-href'],
            'area' => ['href', 'data-href'],
            'iframe' => ['src'],
            'form' => ['action'],
            'meta' => ['content'],
            'link' => ['href']
        ];
    }


    /**
     * @{inheritDoc}
     */
    public function supports($contentType)
    {
        return $contentType == 'text/html';
    }


    /**
     * @{inheritDoc}
     */
    public function processAliasing($html, $mode, Rewriter $rewriter)
    {
        $map = [];

        foreach ($this->htmlAttributes as $tagName => $attributes) {
            $pattern = sprintf('!(<%s\b[^>]+\b(?:%s)=")([^"]+)(")!', $tagName, join('|', $attributes));
            if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $map[$match[2]]= $match;
                }
            }
        }

        return $rewriter->rewriteMatches($html, $mode, $map);
    }
}
