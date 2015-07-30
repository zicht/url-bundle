<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

/**
 * Class HtmlMapper
 *
 * Helper to map urls in an HTML string from internal to public aliasing or vice versa.
 *
 * @package Zicht\Bundle\UrlBundle\Aliasing
 */
final class HtmlMapper
{
    /**
     * Helper function doing the actual work behind internalToPublicHtml and publicToInternalHtml
     *
     * @param string $html
     * @param string $mode Can be either 'internal-to-public' or 'public-to-internal'
     * @param Aliasing $aliaser
     * @return string
     */
    public static function processAliasingInHtml($html, $mode, Aliasing $aliaser)
    {
        if (!preg_match_all('/(?:(?<=href=)|(?<=src=)|(?<=action=))(")([^?"]+)([?"])/', $html, $m, PREG_SET_ORDER)) {
            // early return: if there are no matches, no need for the rest of the processing.
            return $html;
        }

        // sorting the items first will make the 'in_array' further down more efficient.

        $replacements = array();
        foreach ($m as $match) {
            list($src, $open, $url, $close) = $match;

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

            if (!isset($replacements[$url])) {
                $replacements[$url]= array($src, "$open%s$close");
            }
        }

        if (count($replacements)) {
            $replacementMap = array();
            foreach ($aliaser->getAliasingMap(array_keys($replacements), $mode) as $url => $alias) {
                $replacementMap[$replacements[$url][0]]= sprintf($replacements[$url][1], $alias);
            }

            return strtr($html, $replacementMap);
        }
        return $html;
    }
}