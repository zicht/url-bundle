<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Doctrine;

use \Doctrine\ORM\Event\LifecycleEventArgs;
use \Doctrine\ORM\Events;

/**
 * This subscriber handles the creation of aliases
 */
class CreateAliasSubscriber extends BaseSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::postPersist,
            Events::postUpdate,
            Events::postFlush,
        );
    }


    /**
     * Registers a record to be scheduled for aliasing
     *
     * @param LifecycleEventArgs $e
     * @return void
     */
    public function postPersist($e)
    {
        if ($e->getEntity() instanceof $this->className) {
            $this->records[spl_object_hash($e->getEntity())]=$e->getEntity();
        }
    }


    /**
     * Registers a record to be scheduled for aliasing
     *
     * @param LifecycleEventArgs $e
     * @return void
     */
    public function postUpdate($e)
    {
        if ($e->getEntity() instanceof $this->className) {
            $this->records[spl_object_hash($e->getEntity())]=$e->getEntity();
        }
    }


    /**
     * Create the aliases
     *
     * @return void
     */
    public function postFlush()
    {
        if (!$this->enabled) {
            return;
        }

        $aliaser = $this->container->get($this->aliaserServiceId);
        foreach ($this->records as $record) {
            $aliaser->createAlias($record);
        }
    }
}