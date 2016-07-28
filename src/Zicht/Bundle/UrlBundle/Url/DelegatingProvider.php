<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Zicht\Bundle\UrlBundle\Exception\UnsupportedException;
use Zicht\Bundle\FrameworkExtraBundle\Util\SortedList;

/**
 * A provider that delegates to a number of registered providers, ordered by priority.
 */
class DelegatingProvider implements Provider, SuggestableProvider, ListableProvider
{
    /**
     * @var Provider[]
     */
    protected $providers = array();

    /**
     * Initialize the provider
     */
    public function __construct()
    {
        $this->providers = new SortedList();
    }

    /**
     * Add a provider with the specified priority. Higher priority means exactly that ;)
     *
     * @param Provider $provider
     * @param int $priority
     * @return void
     */
    public function addProvider(Provider $provider, $priority = 0)
    {
        $this->providers->insert($provider, $priority);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($object)
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($object)) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function url($object, array $options = array())
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($object)) {
                return $provider->url($object, $options);
            }
        }


        $objectType = is_object($object)
            ? get_class($object)
            : (gettype($object) . ' (' . var_export($object, true) . ')');

        throw new UnsupportedException("Can not render url for {$objectType}");
    }

    /**
     * @{inheritDoc}
     */
    public function suggest($pattern)
    {
        $ret = array();
        foreach ($this->providers as $provider) {
            if ($provider instanceof SuggestableProvider) {
                $ret = array_merge($ret, $provider->suggest($pattern));
            }
        }
        return $ret;
    }

    /**
     * @{inheritDoc}
     */
    public function all(SecurityContextInterface $securityContext)
    {
        $ret = array();
        foreach ($this->providers as $provider) {
            if ($provider instanceof ListableProvider) {
                $ret = array_merge($ret, $provider->all($securityContext));
            }
        }
        return $ret;
    }
}
