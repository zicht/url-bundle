<?php
declare(strict_types=1);
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

// phpcs:disable Zicht.NamingConventions.Functions.NestedDefinition

class ShortUrlManager
{
    /**
     * @var ShortUrlHashGeneratorInterface
     */
    private $shortUrlHashGenerator;

    /**
     * @var Aliasing
     */
    private $aliasing;

    /**
     * @param Aliasing $aliasing
     * @param ShortUrlHashGeneratorInterface|null $shortUrlHashGenerator
     */
    public function __construct(Aliasing $aliasing, ShortUrlHashGeneratorInterface $shortUrlHashGenerator = null)
    {
        $this->aliasing = $aliasing;
        $this->shortUrlHashGenerator = $shortUrlHashGenerator ?: new class() implements ShortUrlHashGeneratorInterface {
            /**
             * {@inheritDoc}
             */
            public function generate($url, $length)
            {
                return substr(hash('sha1', $url), 0, $length);
            }
        };
    }

    /**
     * @param string $url
     * @param string|null $prefix
     * @param int $mode
     * @return UrlAlias
     */
    public function getAlias($url, $prefix = null, $mode = UrlAlias::MOVE)
    {
        if ($prefix && false === strpos($prefix, '/')) {
            $prefix = sprintf('/%s', $prefix);
        }

        $length = 8;
        do {
            // the hash will function as our public url
            $hash = $this->shortUrlHashGenerator->generate($url, $length);
            $publicUrl = $prefix ? sprintf('%s/%s', $prefix, $hash) : $hash;
            $exists = $this->aliasing->getRepository()->findOneByPublicUrl($publicUrl, $mode);
            if ($exists && $exists->getInternalUrl() === $url) {
                return $exists;
            }
            $length++;
            // sanely (sha256 consists of 64 chars) fail if we have a collision after numerous attempts. Something might be wronly implemented.
            if ($length >= 65) {
                throw new \LogicException(sprintf('Found a collision for a hash with %d chars. Something is not right.', $length));
            }
        } while ($exists);

        $this->aliasing->addAlias($publicUrl, $url, $mode);
        return $this->aliasing->findAlias($publicUrl, $url);
    }
}
