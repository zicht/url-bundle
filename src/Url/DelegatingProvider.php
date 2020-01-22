<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Zicht\Bundle\UrlBundle\Exception\UnsupportedException;

/**
 * A provider that delegates to a number of registered providers, ordered by priority.
 */
class DelegatingProvider implements Provider, SuggestableProvider, ListableProvider
{
    /**
     * @var Provider[]
     */
    protected $providers;

    public function __construct()
    {
        $this->providers = [];
    }

    /**
     * Add a provider with the specified priority. Higher priority means exactly that ;)
     *
     * @param int $priority
     * @return void
     */
    public function addProvider(Provider $provider, $priority = 0)
    {
        $this->providers[$priority][] = $provider;
        ksort($this->providers);
    }

    public function supports($object)
    {
        foreach ($this->getProviders() as $provider) {
            if ($provider->supports($object)) {
                return true;
            }
        }
        return false;
    }

    public function url($object, array $options = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        foreach ($this->getProviders() as $provider) {
            if ($provider->supports($object)) {
                return $provider->url($object, $options, $referenceType);
            }
        }

        $objectType = is_object($object)
            ? get_class($object)
            : (gettype($object) . ' (' . var_export($object, true) . ')');

        throw new UnsupportedException("Can not render url for {$objectType}");
    }

    public function suggest($pattern)
    {
        $ret = [];
        foreach ($this->getProviders() as $provider) {
            if ($provider instanceof SuggestableProvider) {
                $ret = array_merge($ret, $provider->suggest($pattern));
            }
        }
        return $ret;
    }

    public function all(AuthorizationCheckerInterface $securityContext)
    {
        $ret = [];
        foreach ($this->getProviders() as $provider) {
            if ($provider instanceof ListableProvider) {
                $ret = array_merge($ret, $provider->all($securityContext));
            }
        }
        return $ret;
    }

    /**
     * @return Provider[]|\Generator
     */
    private function getProviders()
    {
        foreach ($this->providers as $providers) {
            foreach ($providers as $provider) {
                yield $provider;
            }
        }
    }
}
