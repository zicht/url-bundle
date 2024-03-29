<?php
/**
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Doctrine;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * Remove an alias from the aliases
 */
class RemoveAliasSubscriber extends BaseSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
            Events::postFlush,
        ];
    }

    /**
     * Schedule an object's alias to be deleted.
     *
     * @param LifecycleEventArgs $e
     * @return void
     */
    public function preRemove($e)
    {
        $entity = $e->getObject();

        if ($entity instanceof $this->className) {
            // schedule alias removal (this needs to be done before the entity is removed and loses its id)
            $this->container->get($this->aliaserServiceId)->removeAlias($entity, true);
        }
    }

    /**
     * Remove the aliases for all removed records.
     *
     * @return void
     */
    public function postFlush()
    {
        $this->container->get($this->aliaserServiceId)->removeScheduledAliases();
    }
}
