<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Url\Provider;

/**
 * Creates aliases
 */
class Aliaser
{
    /** @var Aliasing */
    protected $aliasing;

    /** @var Provider */
    protected $provider;

    /** @var AliasingStrategy|DefaultAliasingStrategy */
    protected $aliasingStrategy;

    /** @var string */
    protected $conflictingPublicUrlStrategy = Aliasing::STRATEGY_SUFFIX;

    /** @var string */
    protected $conflictingInternalUrlStrategy = Aliasing::STRATEGY_IGNORE;

    /** @var array */
    protected $recursionProtection = [];

    /** @var AccessDecisionManagerInterface */
    protected $decisionManager;

    /** @var array */
    protected $scheduledRemoveAlias;

    /**
     * @param AliasingStrategy $naming
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(Aliasing $aliasing, Provider $provider, AliasingStrategy $naming = null, AccessDecisionManagerInterface $decisionManager = null)
    {
        $this->aliasing = $aliasing;
        $this->provider = $provider;
        if (null === $naming) {
            // TODO: remove this. It should break when not given any. This obstructs tests
            $naming = new DefaultAliasingStrategy();
        }
        $this->aliasingStrategy = $naming;
        $this->decisionManager = $decisionManager;
        $this->scheduledRemoveAlias = [];
    }

    /**
     * @return string
     */
    public function getConflictingInternalUrlStrategy()
    {
        return $this->conflictingInternalUrlStrategy;
    }

    /**
     * @param string $conflictingInternalUrlStrategy
     */
    public function setConflictingInternalUrlStrategy($conflictingInternalUrlStrategy)
    {
        Aliasing::validateInternalConflictingStrategy($conflictingInternalUrlStrategy);

        $this->conflictingInternalUrlStrategy = $conflictingInternalUrlStrategy;
    }

    /**
     * @return string
     */
    public function getConflictingPublicUrlStrategy()
    {
        return $this->conflictingPublicUrlStrategy;
    }

    /**
     * @param string $conflictingPublicUrlStrategy
     */
    public function setConflictingPublicUrlStrategy($conflictingPublicUrlStrategy)
    {
        Aliasing::validatePublicConflictingStrategy($conflictingPublicUrlStrategy);

        $this->conflictingPublicUrlStrategy = $conflictingPublicUrlStrategy;
    }

    /**
     * Create an alias for the provided object.
     *
     * @param mixed $record
     * @param array $action
     * @return bool Whether or not an alias was created.
     */
    public function createAlias($record, $action = ['VIEW'])
    {
        $internalUrl = $this->provider->url($record);

        if (in_array($internalUrl, $this->recursionProtection)) {
            return false;
        }

        $this->recursionProtection[] = $internalUrl;

        if ($this->shouldGenerateAlias($record, $action)) {
            // Don't save an alias if the generated public alias is null
            if (null !== ($generatedAlias = $this->aliasingStrategy->generatePublicAlias($record))) {
                return $this->aliasing->addAlias(
                    $generatedAlias,
                    $internalUrl,
                    UrlAlias::REWRITE,
                    $this->conflictingPublicUrlStrategy,
                    $this->conflictingInternalUrlStrategy
                );
            }
        }

        return false;
    }

    /**
     * Determines whether an alias should be generated for the given record.
     *
     * @param mixed $record
     *
     * @return bool
     */
    public function shouldGenerateAlias($record, array $action = ['VIEW'])
    {
        // without security, everything is considered public
        if (null === $this->decisionManager) {
            return true;
        }

        return $this->decisionManager->decide(new NullToken(), $action, $record);
    }

    /**
     * Removes an alias
     *
     * When $SCHEDULE is true the alias removal is delayed until removeScheduledAliases is called.
     *
     * @param mixed $record
     * @param bool $schedule
     * @return void
     */
    public function removeAlias($record, $schedule = false)
    {
        if ($schedule) {
            // delay removal until flushed
            $this->scheduledRemoveAlias[] = $this->provider->url($record);
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

        $this->scheduledRemoveAlias = [];
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
