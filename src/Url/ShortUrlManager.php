<?php
declare(strict_types=1);
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

class ShortUrlManager
{
    /** @var Aliasing */
    private $aliasing;

    /** @var ShortUrlHashGeneratorInterface */
    private $shortUrlHashGenerator;

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
     * @param int $minLength
     * @param int $mode
     * @return UrlAlias
     */
    public function getAlias($url, $prefix = null, $minLength = 8, $mode = UrlAlias::MOVE)
    {
        if ($prefix && false === strpos($prefix, '/')) {
            $prefix = sprintf('/%s', $prefix);
        }

        do {
            // the hash will function as our public url
            $hash = $this->shortUrlHashGenerator->generate($url, $minLength);
            $publicUrl = $prefix ? sprintf('%s/%s', $prefix, $hash) : $hash;
            $exists = $this->aliasing->getRepository()->findOneByPublicUrl($publicUrl, $mode);
            if ($exists && $exists->getInternalUrl() === $url) {
                return $exists;
            }
            ++$minLength;
            // sanely (sha256 consists of 64 chars) fail if we have a collision after numerous attempts. Something might be wronly implemented.
            if ($minLength >= 65) {
                throw new \LogicException(sprintf('Found a collision for a hash with %d chars. Something is not right.', $minLength));
            }
        } while ($exists);

        $this->aliasing->addAlias($publicUrl, $url, $mode);
        return $this->aliasing->findAlias($publicUrl, $url);
    }
}
