<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use \Symfony\Component\Routing\RouterInterface;

use Doctrine\ORM\EntityManager;
use Zicht\Bundle\UrlBundle\Entity\StaticReference;

/**
 * Static provider holds a set of urls
 */
class StaticProvider implements Provider
{
    /**
     * @var EntityManager
     */
    private $em;


    /**
     * Create the provider with a set of static references, i.e. mappings from name to url.
     *
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param EntityManager $em
     * @param array $refs
     */
    public function __construct(RouterInterface $router, EntityManager $em, array $refs = array())
    {
        $this->refs = $refs;
        $this->router = $router;
        $this->em = $em;
    }

    /**
     * Add all values from database to refs stack
     * Will only be executed when in the config use_static_ref_admin = true
     *
     * @return void
     */
    public function addDbValues()
    {
        /** @var StaticReference $repos */
        $repos = $this->em->getRepository('ZichtUrlBundle:StaticReference');

        /** @var $static_reference StaticReference */
        foreach ($repos->findAll() as $static_reference) {
            $this->refs[$static_reference->getMachineName()] = $static_reference->getUrl();
        }
    }

    /**
     * Add the array as references
     *
     * @param array $refs
     * @return void
     */
    public function addAll(array $refs)
    {
        $this->refs = $refs + $this->refs;
    }


    /**
     * Add a single reference
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function add($name, $value)
    {
        $this->refs[$name] = $value;
    }


    /**
     * @{inheritDoc}
     */
    public function supports($object)
    {
        return is_string($object) && isset($this->refs[$object]);
    }


    /**
     * @{inheritDoc}
     */
    public function url($object, array $options = array())
    {
        $url = ltrim($this->refs[$object], '/');

        if (!preg_match('/^(http|https)/', $url)) {
            $url = $this->router->getContext()->getBaseUrl() . '/' . $url;
        }

        return $url;
    }
}