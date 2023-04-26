<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Zicht\Bundle\UrlBundle\Entity\StaticReference;

/**
 * Static provider holds a set of urls
 */
class DbStaticProvider implements Provider
{
    /** @var EntityManager */
    private $em;

    /** @var RequestStack|null */
    private $requestStack;

    /** @var array Holds the static references */
    private $refs = null;

    /** @var string|null The locale to use when all things fail */
    private $fallback_locale = null;

    /**
     * Create the provider with a set of static references, i.e. mappings from name to url.
     */
    public function __construct(EntityManager $em, RequestStack $requestStack = null)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    /**
     * Add the array as references
     *
     * @return void
     */
    public function addAll()
    {
        // Make sure refs are not null any more, else it keeps checking on every static ref
        $this->refs = [];

        /** @var StaticReference $repos */
        $references = $this->em->getRepository(StaticReference::class)->getAll($this->getLocale());

        foreach ($references as $static_reference) {
            foreach ($static_reference->getTranslations() as $translation) {
                $this->refs[$static_reference->getMachineName()][$translation->getLocale()] = $translation->getUrl();
            }
        }
    }

    public function supports($object)
    {
        $this->checkRefsAreLoaded();

        return is_string($object) && isset($this->refs[$object][$this->getLocale()]);
    }

    public function url($object, array $options = [])
    {
        $this->checkRefsAreLoaded();

        $url = $this->refs[$object][$this->getLocale()];

        if (!preg_match('/^(http|https)/', $url)) {
            if (null !== ($request = $this->getMainRequest())) {
                $url = $request->getBaseUrl() . '/' . ltrim($url, '/');
            }
        }

        return $url;
    }

    /**
     * Returns the locale parameter for the current request, if any.
     *
     * @return mixed
     */
    public function getLocale()
    {
        if (null !== ($request = $this->getMainRequest())) {
            $locale = $request->get('_locale');
        }

        if (!isset($locale)) {
            $locale = $this->fallback_locale;
        }

        return $locale;
    }

    /**
     * Load all static references from the database
     *
     * @return void
     */
    private function checkRefsAreLoaded()
    {
        if (is_null($this->refs)) {
            $this->addAll();
        }
    }

    /**
     * @return Request|null
     */
    private function getMainRequest()
    {
        return (!is_null($this->requestStack)) ? $this->requestStack->getMainRequest() : null;
    }

    /**
     * Fallback locale to use whenever the reference for a specific locale is not set.
     *
     * @param string $locale
     * @return void
     */
    public function setFallbackLocale($locale)
    {
        $this->fallback_locale = $locale;
    }
}
