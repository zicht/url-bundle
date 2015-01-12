<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use \Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use \Zicht\Bundle\UrlBundle\Aliasing\DefaultAliasingStrategy;
use \Zicht\Bundle\UrlBundle\Aliasing\AliasingStrategy;
use \Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use \Zicht\Bundle\UrlBundle\Url\Provider;
use \Zicht\Util\Str;

/**
 * Creates aliases
 */
class Aliaser
{
    protected $aliasing;
    protected $provider;
    protected $aliasingStrategy;

    /**
     * Constructor
     *
     * @param Aliasing $aliasing
     * @param \Zicht\Bundle\UrlBundle\Url\Provider $provider
     * @param AliasingStrategy $naming
     */
    public function __construct(Aliasing $aliasing, Provider $provider, AliasingStrategy $naming = null)
    {
        $this->aliasing = $aliasing;
        $this->provider = $provider;
        if (null === $naming) {
            $naming = new DefaultAliasingStrategy();
        }
        $this->aliasingStrategy = $naming;
    }


    /**
     * Create an alias for the provided object.
     *
     * @param mixed $record
     * @return bool Whether or not an alias was created.
     */
    public function createAlias($record)
    {
        static $recursionProtection = array();
        $ret = false;

        $internalUrl = $this->provider->url($record);
        $generatedAlias = $this->aliasingStrategy->generatePublicAlias($record);
        if ($internalUrl == $this->aliasing->hasInternalAlias($generatedAlias)) {
            // apparently there is already a publicUrl ($generatedAlias) that is associated with $record
            // hence, no need to create a new alias.
            return $ret;
        }

        // if we 've already stored this $generatedAlias, we can safely ignore this call
        if (isset($recursionProtection[$internalUrl]) && $recursionProtection[$internalUrl] == $generatedAlias) {
            return $ret;
        }

        $recursionProtection[$internalUrl] = $generatedAlias;

        if (null !== $generatedAlias) {
            $ret = $this->aliasing->addAlias(
                $generatedAlias,
                $internalUrl,
                UrlAlias::REWRITE,
                Aliasing::STRATEGY_SUFFIX
            );
        }

        return $ret;
    }


    /**
     * Removes an alias
     *
     * @param mixed $record
     * @return void
     */
    public function removeAlias($record)
    {
        $this->aliasing->removeAlias($this->provider->url($record));
    }

    /**
     * Set batch processing on the aliasing service.
     *
     * @param bool $batch
     * @return callable
     */
    public function setIsBatch($batch)
    {
        return $this->aliasing->setIsBatch($batch);
    }
}