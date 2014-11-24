<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Doctrine;

use \Doctrine\Common\EventSubscriber;
use \Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use \Doctrine\ORM\Events;
use \Symfony\Component\DependencyInjection\Container;
use \Zicht\Bundle\UrlBundle\Aliasing\Aliaser;

/**
 * Remove an alias from the aliases
 */
class RemoveAliasSubscriber extends BaseSubscriber
{
    /**
     * @{inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::preRemove,
            Events::postFlush
        );
    }


    /**
     * Schedule an object's alias to be deleted.
     *
     * @param LifecycleEventArgs $e
     * @return void
     */
    public function preRemove($e)
    {
        $entity = $e->getEntity();

        if ($entity instanceof $this->className) {
            // we need a clone, because otherwise the ID will be reset by the entity manager
            $this->records[spl_object_hash($entity)]= clone $entity;
        }
    }


    /**
     * Remove the aliases for all removed records.
     *
     * @return void
     */
    public function postFlush()
    {
        if (count($this->records)) {
            $aliaser = $this->container->get($this->aliaserServiceId);
            foreach ($this->records as $record) {
                $aliaser->removeAlias($record);
            }
        }
    }
}