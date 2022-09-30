<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

use Zicht\Bundle\UrlBundle\Url\Rewriter;

/**
 * Helper to map urls in an HTML string from internal to public aliasing or vice versa.
 */
class HtmlMapper implements UrlMapperInterface
{
    /** @var array Map of [elementName => [attr1, attr2]] where URL's may occur */
    protected $htmlAttributes;

    public function __construct()
    {
        $this->htmlAttributes = [
            'a' => ['href', 'data-href'],
            'area' => ['href', 'data-href'],
            'option' => ['data-href'],
            'iframe' => ['src'],
            'form' => ['action'],
            'meta' => ['content'],
            'link' => ['href'],
        ];
    }

    public function supports($contentType)
    {
        return $contentType === 'text/html';
    }

    public function processAliasing($html, $mode, Rewriter $rewriter)
    {
        $map = [];

        foreach ($this->htmlAttributes as $tagName => $attributes) {
            $pattern = sprintf('!(<%s\b[^>]+\b(?:%s)=")([^"]+)(")!', $tagName, join('|', $attributes));
            if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $map[$match[2]][] = $match;
                }
            }
        }

        return $rewriter->rewriteMatches($html, $mode, $map);
    }

    /**
     * Merges given html attributes with the default attributes.
     *
     * See for a default defined set DependencyInjection/Configuration.php
     *
     * @param array $attributes
     */
    public function addAttributes($attributes)
    {
        $this->htmlAttributes = array_merge_recursive(
            $this->htmlAttributes,
            $attributes
        );
    }
}
