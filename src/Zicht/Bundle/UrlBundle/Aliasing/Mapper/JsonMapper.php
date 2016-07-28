<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

/**
 * Class JsonMapper
 *
 * Helper to map urls in an JSON string from internal to public aliasing or vice versa.
 *
 * @package Zicht\Bundle\UrlBundle\Aliasing
 */
class JsonMapper implements UrlMapperInterface
{
    /**
     * @{inheritDoc}
     */
    public function supports($contentType)
    {
        return $contentType === 'application/json';
    }

    /**
     * @{inheritDoc}
     */
    public function processAliasing($json, $mode, Aliasing $aliaser, $whiteListDomains)
    {
        if (!preg_match_all('/((?:"value"):")([^?"]+)([?"])/', $json, $m, PREG_SET_ORDER)) {
            // early return: if there are no matches, no need for the rest of the processing.
            return $json;
        }

        // sorting the items first will make the 'in_array' further down more efficient.

        $replacements = array();
        foreach ($m as $match) {
            list(, $prefix, $url, $close) = $match;
            // Preg match might result in unwanted spaces such as '/nl/page/33 '
            $url = trim($url);
            $url = str_replace('\/', '/', $url);

            // exclusion (may need to configure these in the future?)
            if (0 === strpos($url, '/bundles/')
                || 0 === strpos($url, '/media/')
                || 0 === strpos($url, '/js/')
                || 0 === strpos($url, '/style/')
                || 0 === strpos($url, '/favicon.ico')
                || 0 === strpos($url, '#')
                || 0 === strpos($url, 'mailto:')
                || 0 === strpos($url, 'tel:')
                || $this->isExternalUrl($url, $whiteListDomains)
            ) {
                continue;
            }

            // check for arguments and remove them
            if (preg_match('!(.*?)((/[^=/]+=[^=/]+)+/?)$!', $url, $m)) {
                $close = $m[2] . $close;
                $url = $m[1];
            }

            $domain = '';

            if (0 === strpos($url, 'http://') || 0 === strpos($url, 'https://')) {

                /**
                 * If we get into this block, that means we are on the whitelist
                 * Therefor we need to add the found domain-information and remove it from the url, so the url can be aliased
                 */
                if (preg_match('!(http(s)?:\/\/[a-zA-Z.]*)\/!', $url, $domainMatch)) {
                    $domain = $domainMatch[1];

                    //remove the domain from the url, so it can be aliased
                    $url = str_replace($domain, '', $url);
                }
            }

            if (!isset($replacements[$url])) {
                $replacements[$url] = [];
            }


            // Build a formatted string by replacing all instances of the found URL's
            // with "%s" as a placeholder.
            $replacements[$url][] = [
                $match[0],
                str_replace('%', '%%', $prefix) . $domain . '%s' . str_replace('%', '%%', $close)
            ];
        }
        
        if (count($replacements)) {
            $replacementMap = array();
            foreach ($aliaser->getAliasingMap(array_keys($replacements), $mode) as $url => $alias) {
                foreach ($replacements[$url] as $pair) {
                    $replacementMap[$pair[0]] = sprintf($pair[1], $alias);
                }
            }

            return strtr($json, $replacementMap);
        }
        return $json;
    }

    /**
     * Check if the url is an external url (and not ignored)
     *
     * @param string $url
     * @param array $whiteListDomains
     * @return bool
     */
    private function isExternalUrl($url, $whiteListDomains)
    {
        $ret = false;

        if (0 === strpos($url, 'http:') || 0 === strpos($url, 'https:')) {
            $ret = true;
        }

        foreach ($whiteListDomains as $domain) {
            if (strpos($url, $domain) > -1) {
                $ret = false;
                break;
            }
        }

        return $ret;
    }
}
