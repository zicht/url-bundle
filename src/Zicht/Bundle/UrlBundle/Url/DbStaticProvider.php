<?php
/**
 * @author    Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use \Symfony\Component\Routing\RouterInterface;

use Doctrine\ORM\EntityManager;
use Zicht\Bundle\UrlBundle\Entity\StaticReference;

/**
 * Static provider holds a set of urls
 */
class DbStaticProvider implements Provider
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Current locale based on router context
     *
     * @var string
     */
    private $locale;

    /**
     * A fallback locale for when the static reference in the current
     * language is not available
     *
     * @var string
     */
    private $fallback_locale;

    /**
     * Create the provider with a set of static references, i.e. mappings from name to url.
     *
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param EntityManager                              $em
     *
     * @internal param array $refs
     */
    public function __construct(RouterInterface $router, EntityManager $em)
    {
        $this->router = $router;
        $this->em     = $em;
        $params       = $router->getContext()->getParameters();

        // Switch to fallback locale when _locale is not available in current context
        if (isset($params['_locale'])) {
            $this->locale = $params['_locale'];
        } else {
            $this->locale = $this->fallback_locale;
        }
    }

    public function setFallbackLocale($locale)
    {
        $this->fallback_locale = $locale;
    }

    /**
     * Add the array as references
     *
     * @param array $refs
     *
     * @return void
     */
    public function addAll(array $refs)
    {
        /** @var StaticReference $repos */
        $repos = $this->em->getRepository('ZichtUrlBundle:StaticReference');

        /** @var $static_reference StaticReference */
        foreach ($repos->findAll() as $static_reference) {
            foreach ($static_reference->getTranslations() as $translation) {
                $this->refs[$static_reference->getMachineName()][$translation->getLocale()] = $translation->getUrl();
            }
        }
    }

    /**
     * Add a single reference
     *
     * @param        $language
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public function add($language, $name, $value)
    {
        $this->refs[$name][$language] = $value;
    }

    /**
     * @{inheritDoc}
     */
    public function supports($object)
    {
        return is_string($object) && isset($this->refs[$object][$this->locale]);
    }

    /**
     * @{inheritDoc}
     */
    public function url($object, array $options = array())
    {
        $url = ltrim($this->refs[$object][$this->locale], '/');

        if (!preg_match('/^(http|https)/', $url)) {
            $url = $this->router->getContext()->getBaseUrl() . '/' . $url;
        }

        return $url;
    }
}