<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper\Traits;

use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

trait MatchingGroupReplaceTrait
{
    /**
     * This convenenience method processes the aliases in the content with a structure matching the following:
     *
     * ['the url' => ['total string that should be replaced', 'the prefix', 'the url', 'the suffix']]
     *
     * e.g.:
     * 'http://example.org/' => ['<a href="http://example.org/"', '<a href="', 'http://example.org/', '"']
     *
     * It replaces contents by replacing all occurrences of the 0 index with the 1 index, concatenated with the
     * aliased url, and the suffix.
     *
     * @param $content
     * @param $mode
     * @param Aliasing $aliaser
     * @param $matchedGroups
     * @return string
     */
    protected function replace($content, $mode, Aliasing $aliaser, $matchedGroups)
    {
        $replacements = [];
        foreach ($aliaser->getAliasingMap(array_keys($matchedGroups), $mode) as $from => $to) {
            if (isset($matchedGroups[$from]) && $from !== $to) {
                list($source, $prefix, $oldUrl, $suffix) = $matchedGroups[$from];
                $replacements[$source] = $prefix . $to . $suffix;
            }
        }

        return strtr($content, $replacements);
    }
}