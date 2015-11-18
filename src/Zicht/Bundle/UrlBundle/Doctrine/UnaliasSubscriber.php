<?php
/**
 * @author Boudewijn Schoon <boudewijn@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

/**
 * Custom subscriber which looks for entity creation and update and ensures that all urls in specified html text
 * fields are unaliased, i.e. /aliased-page => /nl/page/3.
 */
class UnaliasSubscriber implements EventSubscriber
{
    /**
     * Configuration from zicht_url:unalias_subscriber.
     *
     * It contains the following structure:
     * array(
     *  'enabled' => true
     *  'entities' => array(
     *    'Zicht\Bundle\SiteBundle\Entity\Page\ContentPage' => array('intro', 'body')
     *    'Zicht\Bundle\SiteBundle\Entity\Page\NewsPage' => array('intro', 'body'),
     *    etc
     *  )
     * )
     *
     * @var array
     */
    protected $config;

    /**
     * @var
     */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct($container, array $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * @{inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return $this->getEnabled() ? array('prePersist', 'preUpdate') : array();
    }

    /**
     * @{inheritDoc}
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->publicToInternalHtmlListener($args);
    }

    /**
     * @{inheritDoc}
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        if ($this->publicToInternalHtmlListener($args) > 0) {
            // we must recompute the entity change set, otherwise none of our changes will be applied
            $entity = $args->getEntity();
            $entityManager = $args->getEntityManager();
            $metadata = $entityManager->getClassMetadata(get_class($entity));
            $entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet($metadata, $entity);
        }
    }

    /**
     * The configured fields for configured entities are passed through the publicToInternalHtml filter
     *
     * @param LifecycleEventArgs $args
     * @return int
     */
    protected function publicToInternalHtmlListener(LifecycleEventArgs $args)
    {
        /** @var Aliasing $aliasing */
        $aliasing = $this->container->get('zicht_url.aliasing');
        $entities = $this->getEntities();

        $changes = 0;
        $entity = $args->getEntity();

        foreach ($entities as $className => $fields) {
            if ($entity instanceof $className) {
                foreach ($fields as $field) {
                    $aliased = $this->getFromEntity($entity, $field);
                    if (count($aliased)) {
                        $unaliased = $aliasing->publicToInternalHtml($aliased);
                        if ($aliased !== $unaliased) {
                            $this->setIntoEntity($entity, $field, $unaliased);
                            $changes += 1;
                        }
                    }
                }
            }
        }
        return $changes;
    }

    /**
     * Tries to get the value of $field out of $entity
     *
     * @param object $entity
     * @param string $field
     * @return mixed
     */
    protected function getFromEntity($entity, $field)
    {
        // try calling getter, i.e. $entity->getField
        $getters = array(sprintf('get%s', ucfirst($field)));
        foreach ($getters as $getter) {
            if (method_exists($entity, $getter)) {
                return call_user_func(array($entity, $getter));
            }
        }

        return null;
    }

    /**
     * Tries to set $html to the $field out of $entity
     *
     * @param object $entity
     * @param string $field
     * @param string $html
     * @return mixed
     */
    private function setIntoEntity($entity, $field, $html)
    {
        // try calling setter, i.e. $entity->setField
        $setters = array(sprintf('set%s', ucfirst($field)));
        foreach ($setters as $setter) {
            if (method_exists($entity, $setter)) {
                call_user_func(array($entity, $setter), $html);
            }
        }
    }

    /**
     * Returns true when enabled in the configuration
     *
     * @return mixed
     */
    private function getEnabled()
    {
        return $this->config['enabled'];
    }

    /**
     * Returns the array of configured entities
     *
     * @return mixed
     */
    private function getEntities()
    {
        return $this->config['entities'];
    }
}