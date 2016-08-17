<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;


class Rewriter
{
    public function __construct()
    {
    }


    /**
     * @param string[] $urls
     * @param array $mappings
     * @param string[] $localDomains
     * @return array
     */
    public function rewrite(array $urls, array $mappings, array $localDomains)
    {
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
                if (!in_array($parts['host'], $localDomains)) {
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
}