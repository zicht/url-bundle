<?php
/**
 * @author Boudewijn Schoon <boudewijn@zicht.nl>
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

/**
 * Class XmlMapper
 *
 * Helper to map urls in an XML string from internal to public aliasing or vice versa.
 *
 * @package Zicht\Bundle\UrlBundle\Aliasing
 */
class XmlMapper implements UrlMapperInterface
{

    /**
     * Check if the mapper supports the given contentType
     *
     * @param string $contentType
     *
     * @return boolean
     */
    public function supports($contentType)
    {
        return ($contentType === 'text/xml' || $contentType === 'application/xml');
    }

    /**
     * Implementation to transform the given content to proper public aliases
     *
     * @param string $content
     * @param string $mode
     * @param Aliasing $aliaser
     * @param array|null $whiteListDomains
     * 
     * @return mixed
     */
    public function processAliasing($content, $mode, Aliasing $aliaser, $whiteListDomains)
    {
        $expression = '/<loc>(?:https?:\/\/[^\/]+)([^#?]+?)(?:[#?].*)?<\/loc>/';

        // 'ref' in the regex is no typo here. A look-back assertion must be of fixed length, so this is a minor
        // optimization.
        if (!preg_match_all($expression, $content, $matches)) {
            // early return: if there are no matches, no need for the rest of the processing.
            return $content;
        }

        // sorting the items first will make the 'in_array' further down more efficient.
        sort($matches[1]);

        $urls = array();

        foreach ($matches[1] as $url) {
            // exclusion (may need to configure these in the future?)
            if (
                0 === strpos($url, '/bundles/')
                || 0 === strpos($url, '/media/')
                || 0 === strpos($url, '/js/')
                || 0 === strpos($url, '/style/')
                || 0 === strpos($url, '/favicon.ico')
                || 0 === strpos($url, '#')
                || 0 === strpos($url, 'mailto:')
                || 0 === strpos($url, 'tel:')
                || 0 === strpos($url, 'http:')
                || 0 === strpos($url, 'https:')
            ) {
                continue;
            }

            if (!in_array($url, $urls)) {
                $urls[] = $url;
            }
        }

        if (count($urls)) {
            foreach ($aliaser->getAliasingMap($urls, $mode) as $k => $v) {
                $content = preg_replace('/'. addcslashes($k, '/').'\b/', $v, $content);
            }
        }

        return $content;
    }
}