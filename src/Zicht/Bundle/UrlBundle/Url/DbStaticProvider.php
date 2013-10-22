<?php
/**
 * @author    Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use \Doctrine\ORM\EntityManager;
use \Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\Routing\RouterInterface;
use \Zicht\Bundle\UrlBundle\Entity\StaticReference;

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
     * Current master request (needed for locale and base url)
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * Holds the static references
     *
     * @var array
     */
    private $refs = null;

    /**
     * Create the provider with a set of static references, i.e. mappings from name to url.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    /**
     * Stores the request
     *
     * @param Request $r
     * @return void
     */
    public function setRequest(Request $r)
    {
        $this->request = $r;
    }

    /**
     * Add the array as references
     *
     * @return void
     */
    public function addAll()
    {
        // Make sure refs are not null any more, else it keeps checking on every static ref
        $this->refs = array();

        /** @var StaticReference $repos */
        $q = $this->em->getRepository('ZichtUrlBundle:StaticReference')
            ->createQueryBuilder('r')
            ->addSelect('t')
            ->innerJoin('r.translations', 't')
            ->andWhere('t.locale=:locale')
            ->getQuery()
        ;

        /** @var $static_reference StaticReference */
        $localeCode = $this->getLocale();
        foreach ($q->execute(array(':locale' => $localeCode)) as $static_reference) {
            foreach ($static_reference->getTranslations() as $translation) {
                $this->refs[$static_reference->getMachineName()][$translation->getLocale()] = $translation->getUrl();
            }
        }
    }

    /**
     * @{inheritDoc}
     */
    public function supports($object)
    {
        $this->checkRefsAreLoaded();

        return is_string($object) && isset($this->refs[$object][$this->getLocale()]);
    }

    /**
     * @{inheritDoc}
     */
    public function url($object, array $options = array())
    {
        $this->checkRefsAreLoaded();

        $url = $this->refs[$object][$this->getLocale()];

        if (!preg_match('/^(http|https)/', $url)) {
            $url = $this->request->getBaseUrl() . '/' . ltrim($url, '/');
        }

        return $url;
    }

    public function getLocale()
    {
        return $this->request->get('_locale');
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
}