<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

/**
 * Class Rewriter
 */
class Rewriter
{
    private $localDomains = [];

    /**
     * Constructor
     *
     * @param Aliasing $aliasing
     */
    public function __construct(Aliasing $aliasing)
    {
        $this->aliasing = $aliasing;
    }

    /**
     * Set a list of domains in urls to consider 'local', i.e. process the 'path' while keeping the domain in tact.
     *
     * @param string[] $localDomains
     */
    public function setLocalDomains($localDomains)
    {
        $this->localDomains = $localDomains;
    }

    /**
     * Rewrite all $urls, given the $mappings map of mappings (from => to), within the context of
     * host names $localDomains.
     *
     * Example:
     *
     *     rewrite(['http://example.org/foo?x=y'], ['/foo' => '/bar'], ['example.org']);
     *
     *
     * Would return the following mapping:
     *
     * ['http://example.org/foo?x=y' => 'http://example.org/bar?x=y']
     *
     * @param string[] $urls
     * @param string $mode
     * @return array
     */
    public function rewrite(array $urls, $mode)
    {
        $mappings = $this->aliasing->getAliasingMap(
            array_map(
                [$this, 'extractPath'],
                $urls
            ),
            $mode
        );

        $ret = [];
        foreach ($urls as $url) {
            if (isset($mappings[$url])) {
                // early match, if the value can be mapping directly, used that.
                $ret[$url] = $mappings[$url];
                continue;
            }

            $parts = $this->parseUrl($url);

            if (!isset($parts['path'])) {
                // no path, nothing to map.
                $ret[$url] = $url;
                continue;
            }

            if (isset($parts['host'])) {
                if (!in_array($parts['host'], $this->localDomains)) {
                    // external url, don't do mapping.
                    $ret[$url] = $url;
                    continue;
                }
            }

            // don't rewrite this.
            if (isset($parts['user']) || isset($parts['password'])) {
                $ret[$url]=  $url;
                continue;
            }

            $rewritten = '';
            if (isset($parts['scheme'])) {
                $rewritten .= $parts['scheme'] . ':';
            }
            if (isset($parts['host'])) {
                $rewritten .= '//' . $parts['host'];
            }
            if (isset($parts['port'])) {
                $rewritten .= ':' . $parts['port'];
            }
            if (isset($parts['path'])) {
                if (isset($mappings[$parts['path']])) {
                    $rewritten .= $mappings[$parts['path']];
                } else {
                    // no match on path level, keep as-is
                    $ret[$url] = $url;
                    continue;
                }
            }
            if (isset($parts['params'])) {
                $rewritten .= '/' . $parts['params'];
            }
            if (isset($parts['query'])) {
                $rewritten .= '?' . $parts['query'];
            }
            if (isset($parts['fragment'])) {
                $rewritten .= '#' . $parts['fragment'];
            }

            $ret[$url] = $rewritten;
        }
        return $ret;
    }


    /**
     * Extract the path of the URL which is considered for aliasing.
     *
     * @param string $url
     * @return string|null
     */
    public function extractPath($url)
    {
        $parts = $this->parseUrl($url);

        if (!isset($parts['path'])) {
            return null;
        }
        // ignore non-http schemes
        if (isset($parts['scheme']) && !in_array($parts['scheme'], ['http', 'https'])) {
            return null;
        }

        return $parts['path'];
    }


    /**
     * Parse the url, and additionally add a 'parameters' part which is defined as follows:
     *
     * The parameters may occur after the path, separated by slashes, where each of the key value pairs are
     * separated by '='.
     *
     * e.g.:
     *      /this/is/the/path/while=these/are=paremeters
     *      ^----- path ----^ ^------- params ---------^
     *
     * @param string $url
     * @return mixed
     */
    public function parseUrl($url)
    {
        $ret = parse_url($url);

        if (isset($ret['path'])) {
            $parts = explode('/', $ret['path']);
            $params = [];
            while (false !== strpos(end($parts), '=')) {
                array_push($params, array_pop($parts));
            }
            if (count($params)) {
                $ret['path'] = join('/', $parts);
                $ret['params'] = join('/', $params);
            }
        }

        return $ret;
    }


    /**
     * This convenenience method processes the urls in the content with a structure matching the following:
     *
     * ['the url' => ['total string that should be replaced', 'the prefix', 'the url', 'the suffix']]
     *
     * e.g.:
     * 'http://example.org/' => ['<a href="http://example.org/"', '<a href="', 'http://example.org/', '"']
     *
     * It replaces contents by replacing all occurrences of the 0 index with the 1 index, concatenated with the
     * rewritten url, and the suffix.
     *
     * @param string $content
     * @param string $mode
     * @param mixed $matchedGroups
     * @return string
     */
    public function rewriteMatches($content, $mode, $matchedGroups)
    {
        $replacements = [];
        foreach ($this->rewrite(array_keys($matchedGroups), $mode) as $from => $to) {
            if (isset($matchedGroups[$from]) && $from !== $to) {
                foreach ($matchedGroups[$from] as list($source, $prefix, $oldUrl, $suffix)) {
                    $replacements[$source] = $prefix . $to . $suffix;
                }
            }
        }

        return strtr($content, $replacements);
    }
}
