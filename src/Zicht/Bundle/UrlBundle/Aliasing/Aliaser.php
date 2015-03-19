<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use \Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use \Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
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
     * @var AccessDecisionManagerInterface
     */
    protected $decisionManager;

    /**
     * @var array
     */
    protected $scheduledRemoveAlias;

    /**
     * Constructor
     *
     * @param Aliasing $aliasing
     * @param \Zicht\Bundle\UrlBundle\Url\Provider $provider
     * @param AliasingStrategy $naming
     */
    public function __construct(Aliasing $aliasing, Provider $provider, AliasingStrategy $naming = null, AccessDecisionManagerInterface $decisionManager = null)
    {
        $this->aliasing = $aliasing;
        $this->provider = $provider;
        if (null === $naming) {
            $naming = new DefaultAliasingStrategy();
        }
        $this->aliasingStrategy = $naming;
        $this->decisionManager = $decisionManager;
        $this->scheduledRemoveAlias = array();
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

        if (null !== $this->decisionManager && !$this->decisionManager->decide(new AnonymousToken('main', 'anonymous'), array('VIEW'), $record)) {
            $this->aliasing->removeAlias($internalUrl);
        } else {
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
        }

        return $ret;
    }


    /**
     * Removes an alias
     *
     * When $SCHEDULE is true the alias removal is delayed until removeScheduledAliases is called.
     *
     * @param mixed $record
     * @param boolean $schedule
     * @return void
     */
    public function removeAlias($record, $schedule = false)
    {
        if ($schedule) {
            // delay removal until flushed
            $this->scheduledRemoveAlias [] = $this->provider->url($record);
        } else {
            $this->aliasing->removeAlias($this->provider->url($record));
        }
    }

    /**
     * Remove scheduled aliases
     *
     * Example:
     * $aliaser->removeAlias($page, true);
     * # alias for $page is scheduled for removal, i.e. not yet actually removed
     * $aliaser->removeScheduledAliases()
     * # now the alias for $page is removed
     *
     * @return void
     */
    public function removeScheduledAliases()
    {
        foreach ($this->scheduledRemoveAlias as $alias) {
            $this->aliasing->removeAlias($alias);
        }

        $this->scheduledRemoveAlias = array();
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