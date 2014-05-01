<?php
/**
 * @author Oskar van Velden <oskar@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
  */

namespace Zicht\Bundle\UrlBundle\Doctrine;

use \Doctrine\Common\EventArgs;
use \Doctrine\Common\EventSubscriber;
use \Doctrine\ORM\Events;
use \Doctrine\ORM\Event\LifecycleEventArgs;
use \Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\Container;
use Zicht\Bundle\UrlBundle\Aliasing\Aliaser;

/**
 * The subscriber that manages updates, persists of registered entities
 */
class AliasSubscriber implements EventSubscriber
{
    /** @var Container $container */
    private $container;

    private $entityClasses = array();

    /**
     * Construct the aliasing listener.
     *
     * @param Container $container
     * @internal param $aliasing
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Returns the subscribed events for this listener.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'preRemove',
            'postPersist',
            'postUpdate'
        );
    }

    public function addEntityClass($entityClass)
    {
        if(!in_array($entityClass, $this->entityClasses)) {
            $this->entityClasses[] = $entityClass;
        }
    }

    private function supportsClass($class)
    {
        return in_array($class, $this->entityClasses);
    }

    /** @return Aliaser */
    private function getAliaserService()
    {
        return $this->container->get('zicht_url.aliaser');
    }

    private function createAlias($entity)
    {
        if($this->supportsClass(get_class($entity))) {
            $this->getAliaserService()->createAlias($entity);
        }
    }

    /**
     * Creates an alias for the entity (if not exists yet)
     *
     * @param LifeCycleEventArgs $eventArgs
     * @return void
     */
    public function postUpdate($eventArgs)
    {
        //TODO: check if the title has changed, so we need to replace the current alias
        //TODO: check if the current alias (if exists) was based on the previous title - this is needed, to prevent that a custom alias gets replaced

        //TODO: this should probably be implemented in the Aliaser-class

//        $changeset = $eventArgs->getEntityChangeSet();
//        var_dump($changeset);
//        exit;

        $this->createAlias($eventArgs->getEntity());
    }

    /**
     * Creates an alias for the entity (if not exists yet)
     *
     * @param LifeCycleEventArgs $eventArgs
     * @return void
     */
    public function postPersist($eventArgs)
    {
        $this->createAlias($eventArgs->getEntity());
    }

    /**
     * Removes the alias attached to the entity
     *
     * @param LifeCycleEventArgs $eventArgs
     * @return void
     */
    public function preRemove($eventArgs)
    {
        //TODO: remove alias
    }
}