<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Url\Params\UriParser;

class PublicAliasHandler
{
    const SLASH_SUFFIX_ABSTAIN = 'abstain';
    const SLASH_SUFFIX_ACCEPT = 'accept';
    const SLASH_SUFFIX_REDIRECT_PERM = 'redirect-301';
    const SLASH_SUFFIX_REDIRECT_TEMP = 'redirect-302';

    /** @var Aliasing */
    private $aliasing;

    /** @var string[] */
    protected $excludePatterns = [];

    /** @var bool */
    protected $isParamsEnabled = false;

    /** @var string|null */
    protected $slashSuffixHandling = self::SLASH_SUFFIX_ABSTAIN;

    public function __construct(Aliasing $aliasing)
    {
        $this->aliasing = $aliasing;
    }

    /**
     * Exclude patterns from aliasing
     *
     * @param string[] $excludePatterns
     */
    public function setExcludePatterns($excludePatterns)
    {
        $this->excludePatterns = $excludePatterns;
    }

    /**
     * Whether or not to consider URL parameters (key/value pairs at the end of the URL)
     *
     * @param bool $isParamsEnabled
     */
    public function setIsParamsEnabled($isParamsEnabled)
    {
        $this->isParamsEnabled = $isParamsEnabled;
    }

    /**
     * Set the mode for handling paths witch have a `/` slash suffixed. Must be one of self::SLASH_SUFFIX_*
     *
     * @param string $slashSuffixHandling
     */
    public function setSlashSuffixHandling($slashSuffixHandling)
    {
        $this->slashSuffixHandling = $slashSuffixHandling;
    }

    /**
     * Returns true if the URL matches any of the exclude patterns
     *
     * @param string $url
     * @return bool
     */
    protected function isExcluded($url)
    {
        foreach ($this->excludePatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $publicUrl
     * @return UrlAlias|void
     */
    public function handlePublicUrl($publicUrl)
    {
        if ($this->isExcluded($publicUrl)) {
            // don't process urls which are marked as excluded.
            return;
        }

        $originalUrl = $publicUrl;
        $queryString = $originalQueryString = '';
        if (false !== ($queryMarkPos = strpos($publicUrl, '?'))) {
            $publicUrl = substr($originalUrl, 0, $queryMarkPos);
            $queryString = $originalQueryString = substr($originalUrl, $queryMarkPos);
        }

        if ($this->isParamsEnabled) {
            $parts = explode('/', $publicUrl);
            $params = [];
            while (false !== strpos(end($parts), '=')) {
                $params[] = array_pop($parts);
            }
            if ($params) {
                $publicUrl = implode('/', $parts);

                $parser = new UriParser();
                parse_str(substr($queryString, 1), $queryParams);
                $queryString = '?' . http_build_query(array_merge_recursive($parser->parseUri(implode('/', array_reverse($params))), $queryParams));

                if (!$this->aliasing->hasInternalAlias($publicUrl, false)) {
                    return new UrlAlias($originalUrl, $publicUrl . $queryString, UrlAlias::REWRITE);
                }
            }
        }

        $tryPublicUrls = [];
        if ($queryString !== '') {
            $tryPublicUrls[$publicUrl . $queryString] = null;
        }
        $tryPublicUrls[$publicUrl] = null;

        if ($this->slashSuffixHandling !== static::SLASH_SUFFIX_ABSTAIN && substr($publicUrl, -1) === '/' && rtrim($publicUrl, '/') !== '') {
            if ($queryString !== '') {
                $tryPublicUrls[rtrim($publicUrl, '/') . $queryString] = $this->slashSuffixHandling;
            }
            $tryPublicUrls[rtrim($publicUrl, '/')] = $this->slashSuffixHandling;
        }

        $urlAliases = $this->aliasing->getInternalAliases(array_keys($tryPublicUrls));

        if (count($urlAliases) === 0) {
            return;
        }

        foreach ($tryPublicUrls as $tryPublicUrl => $handlingMode) {
            if (!array_key_exists($tryPublicUrl, $urlAliases)) {
                continue;
            }

            $urlAlias = $urlAliases[$tryPublicUrl];

            switch ($handlingMode) {
                case static::SLASH_SUFFIX_REDIRECT_TEMP:
                    $url = $urlAlias->getMode() === UrlAlias::ALIAS ? $urlAlias->getInternalUrl() : $urlAlias->getPublicUrl(); // Same mode? Use internal URL directly, don't go redirecting twice...
                    return new UrlAlias($originalUrl, $url, UrlAlias::ALIAS);

                case static::SLASH_SUFFIX_REDIRECT_PERM:
                    $url = $urlAlias->getMode() === UrlAlias::MOVE ? $urlAlias->getInternalUrl() : $urlAlias->getPublicUrl(); // Same mode? Use internal URL directly, don't go redirecting twice...
                    return new UrlAlias($originalUrl, $url, UrlAlias::MOVE);

                case static::SLASH_SUFFIX_ACCEPT:
                    // Continue as if the correct URL (without '/' suffix) was requested. Could result in duplicate content disqualifications
                default: // $handlingMode === null
                    return $urlAlias;
            }
        }
    }
}
