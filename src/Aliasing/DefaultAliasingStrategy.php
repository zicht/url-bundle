<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use Zicht\Util\Str;

/**
 * An aliasing strategy which simply puts the systemized title at the specified base path (default is root)
 */
class DefaultAliasingStrategy implements AliasingStrategy
{
    /** @var string */
    private $basePath;

    /**
     * Construct with the specified base path
     *
     * @param string $basePath
     */
    public function __construct($basePath = '/')
    {
        $this->basePath = $basePath;
    }

    /**
     * Returns the calculated public alias for the specified object.
     *
     * @param string|Aliasable|object|\Stringable $subject
     * @param string $currentAlias
     * @return string|null
     * @throws \InvalidArgumentException
     */
    public function generatePublicAlias($subject, $currentAlias = '')
    {
        if (is_object($subject)) {
            if ($subject instanceof Aliasable) {
                $subject = (string)$subject->getAliasTitle();
            } elseif (method_exists($subject, 'getTitle')) {
                $subject = (string)$subject->getTitle();
            } else {
                $subject = (string)$subject;
            }
        }
        if (!is_string($subject)) {
            throw new \InvalidArgumentException('Expected a string or object as subject, got ' . gettype($subject));
        }

        if ($alias = $this->toAlias($subject)) {
            return $this->basePath . $alias;
        }
        return null;
    }

    /**
     * Systemizes the specified string.
     *
     * @param string $title
     * @return string
     */
    protected function toAlias($title)
    {
        return Str::systemize($title);
    }
}
