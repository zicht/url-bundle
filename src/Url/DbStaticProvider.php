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

    /** @var null|RequestStack */
    private $requestStack;

    /** @var array Holds the static references */
    private $refs = null;

    /** @var null|string The locale to use when all things fail */
    private $fallback_locale = null;

    /**
     * Create the provider with a set of static references, i.e. mappings from name to url.
     *
     * @param EntityManager $em
     * @param RequestStack|null $requestStack
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
        $references = $this->em->getRepository('ZichtUrlBundle:StaticReference')->getAll($this->getLocale());

        foreach ($references as $static_reference) {
            foreach ($static_reference->getTranslations() as $translation) {
                $this->refs[$static_reference->getMachineName()][$translation->getLocale()] = $translation->getUrl();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($object)
    {
        $this->checkRefsAreLoaded();

        return is_string($object) && isset($this->refs[$object][$this->getLocale()]);
    }

    /**
     * {@inheritDoc}
     */
    public function url($object, array $options = [])
    {
        $this->checkRefsAreLoaded();

        $url = $this->refs[$object][$this->getLocale()];

        if (!preg_match('/^(http|https)/', $url)) {
            if (null !== ($request = $this->getMasterRequest())) {
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
        if (null !== ($request = $this->getMasterRequest())) {
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
     * @return null|Request
     */
    private function getMasterRequest()
    {
        return (!is_null($this->requestStack)) ? $this->requestStack->getMasterRequest() : null;
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
