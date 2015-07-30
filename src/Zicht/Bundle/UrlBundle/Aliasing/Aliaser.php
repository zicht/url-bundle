<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use \Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use \Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
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
     * @param AccessDecisionManagerInterface $decisionManager
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
        $internalUrl = $this->provider->url($record);
        if (in_array($internalUrl, $this->recursionProtection)) {
            return false;
        }
        $this->recursionProtection[]= $internalUrl;


        $ret = false;
        if (null !== $this->decisionManager && !$this->decisionManager->decide(new AnonymousToken('main', 'anonymous'), array('VIEW'), $record)) {
            $this->aliasing->removeAlias($internalUrl);
        } else {
            // if the url already is aliased, no need to regenerate.
            if (!$this->aliasing->hasPublicAlias($internalUrl)) {
                $generatedAlias = $this->aliasingStrategy->generatePublicAlias($record);

                if (null !== $generatedAlias) {
                    $ret = $this->aliasing->addAlias(
                        $generatedAlias,
                        $internalUrl,
                        UrlAlias::REWRITE,
                        Aliasing::STRATEGY_SUFFIX
                    );
                }
            }
        }

        return $ret;
    }
    private $recursionProtection = array();


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
            $this->scheduledRemoveAlias []= $this->provider->url($record);
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