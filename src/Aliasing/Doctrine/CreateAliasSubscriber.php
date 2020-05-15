<?php
/**
 * @copyright Zicht online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing\Doctrine;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

/**
 * This subscriber handles the creation of aliases
 */
class CreateAliasSubscriber extends BaseSubscriber
{
    /**
     * @var bool
     */
    private $isHandling = false;

    /**
     * @var array
     */
    private $records = [];

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        if ($this->enabled) {
            return [
                Events::postPersist,
                Events::postUpdate,
                Events::postFlush,
            ];
        } else {
            return [];
        }
    }

    /**
     * Add a entity to record list for postFlush processing.
     *
     * @param object $entity
     * @param array $action
     */
    protected function addRecord($entity, array $action = [])
    {
        if ($entity instanceof $this->className) {
            if (false !== ($index = array_search($entity, array_column($this->records, 0), true))) {
                if (!in_array($this->records[$index], $action)) {
                    $this->records[$index][] = $action;
                }
            } else {
                $this->records[] = [$entity, $action];
            }
        }
    }

    /**
     * Registers a record to be scheduled for aliasing
     *
     * @param LifecycleEventArgs $e
     * @return void
     */
    public function postPersist($e)
    {
        $this->addRecord($e->getEntity(), ['ACTION_POST_PERSIST']);
    }


    /**
     * Registers a record to be scheduled for aliasing
     *
     * @param LifecycleEventArgs $e
     * @return void
     */
    public function postUpdate($e)
    {
        $this->addRecord($e->getEntity(), ['ACTION_POST_UPDATE']);
    }


    /**
     * Create the aliases
     *
     * @return void
     */
    public function postFlush()
    {
        if (!$this->enabled || $this->isHandling) {
            return;
        }

        $this->isHandling = true;

        $aliaser = $this->container->get($this->aliaserServiceId);

        while (list($record, $action) = array_shift($this->records)) {
            $aliaser->createAlias($record, $action);
        }

        $this->isHandling = false;
    }
}
