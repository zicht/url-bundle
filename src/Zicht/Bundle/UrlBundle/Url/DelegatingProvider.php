<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Zicht\Bundle\UrlBundle\Exception\UnsupportedException;

class DelegatingProvider implements Provider, SuggestableProvider
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
        $this->providers = new \SplPriorityQueue();
    }


    /**
     * Add a provider with the specified priority. Higher priority means exactly that ;)
     */
    function addProvider(Provider $provider, $priority = 0)
    {
        $this->providers->insert($provider, $priority);
    }

    /**
     * {@inheritDoc}
     */
    function supports($object) {
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
    function url($object, array $options = array()) {
        foreach ($this->providers as $provider) {
            if ($provider->supports($object)) {
                return $provider->url($object, $options);
            }
        }
        throw new UnsupportedException(
            "Can not render url for " . (
                is_object($object)
                    ? get_class($object)
                    : (gettype($object) . ' (' . var_export($object, true) . ')')
            )
        );
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
}