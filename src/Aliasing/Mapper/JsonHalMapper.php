<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Mapper;

use Symfony\Component\HttpFoundation\JsonResponse;
use Zicht\Bundle\UrlBundle\Url\Rewriter;

/**
 * Helper to map urls in an JSON HAL string from internal to public aliasing or vice versa.
 */
class JsonHalMapper extends AbstractMapper
{
    public function __construct()
    {
        parent::__construct(['application/hal+json'], '/("href":")(.*)(")/U');
    }

    public function processAliasing($content, $mode, Rewriter $rewriter)
    {
        $options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

        // Json is escaped by default we remove the escaping to replace the url. The escaping is added after
        $content = json_encode(json_decode($content, false, 512, $options), JSON_UNESCAPED_SLASHES);

        if (!preg_match_all($this->pattern, $content, $matches, PREG_SET_ORDER)) {
            // early return: if there are no matches, no need for the rest of the processing.
            return $content;
        }
        $groups = [];
        foreach ($matches as $match) {
            $groups[$match[2]][] = $match;
        }

        $content = $rewriter->rewriteMatches($content, $mode, $groups);

        // Use the JsonResponse setData method so the content is escaped as we are expecting.
        $jsonResponse = new JsonResponse(json_decode($content));

        return $jsonResponse->getContent();
    }
}
