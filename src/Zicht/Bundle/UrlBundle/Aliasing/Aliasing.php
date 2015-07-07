<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use \Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use \Doctrine\ORM\EntityManager;
use \Zicht\Bundle\UrlBundle\Aliasing\UrlAliasRepositoryInterface;
use \Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * Service that contains aliasing information
 */
class Aliasing
{
    /**
     * Overwrite an alias, if exists.
     *
     * @see addAlias
     */
    const STRATEGY_OVERWRITE    = 'overwrite';

    /**
     * Keep existing aliases and do nothing
     *
     * @see addAlias
     */
    const STRATEGY_KEEP         = 'keep';

    /**
     * Suffix existing aliases.
     *
     * @see addAlias
     */
    const STRATEGY_SUFFIX       = 'suffix';

    /** @var Doctrine\ORM\EntityManager  */
    protected $manager;

    protected $isBatch = false;

    /**
     * Initialize with doctrine
     *
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository('ZichtUrlBundle:UrlAlias');
        $this->batch = array();
    }


    /**
     * Checks if the passed public url was available
     *
     * @param string $publicUrl
     * @param bool $asObject
     * @param null $mode
     * @return null
     */
    public function hasInternalAlias($publicUrl, $asObject = false, $mode = null)
    {
        $ret = null;
        if (isset($this->batch[$publicUrl])) {
            $alias = $this->batch[$publicUrl];
        } else {
            $where = array('public_url' => $publicUrl);
            if (null !== $mode) {
                $where['mode'] = $mode;
            }
            $alias = $this->getRepository()->findOneBy($where);
        }
        if ($alias) {
            $ret = ($asObject ? $alias : $alias->getInternalUrl());
        }

        return $ret;
    }


    /**
     * Check if the passed internal URL has a public url alias.
     *
     * @param string $internalUrl
     * @param bool $asObject
     * @return null
     */
    public function hasPublicAlias($internalUrl, $asObject = false)
    {
        $ret = null;

        $params = array('internal_url' => $internalUrl, 'mode' => UrlAlias::REWRITE);
        if ($alias = $this->getRepository()->findOneBy($params, array('id' => 'DESC'))) {
            $ret = ($asObject ? $alias : $alias->getPublicUrl());
        }

        return $ret;
    }

    public function findAlias($publicUrl, $internalUrl)
    {
        $ret = null;

        $params = array('public_url' => $publicUrl, 'internal_url' => $internalUrl);
        if ($alias = $this->getRepository()->findOneBy($params)) {
            $ret = $alias;
        }

        return $ret;
    }

    /**
     * Returns the repository used for storing the aliases
     *
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
    }


    /**
     * Add an alias
     *
     * @param string $publicUrl
     * @param string $internalUrl
     * @param int $type
     * @param string $strategy
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function addAlias($publicUrl, $internalUrl, $type, $strategy = self::STRATEGY_OVERWRITE)
    {
        $ret = false;
        /** @var $alias UrlAlias */

        if ($alias = $this->hasInternalAlias($publicUrl, true)) {
            switch ($strategy) {
                case self::STRATEGY_OVERWRITE:
                    $alias->setInternalUrl($internalUrl);
                    $this->save($alias);
                    $ret = true;
                    break;
                case self::STRATEGY_KEEP:
                    // do nothing intentionally
                    break;
                case self::STRATEGY_SUFFIX:
                    $original = $publicUrl;
                    $i = 1;
                    do {
                        $publicUrl = $original . '-' . ($i ++);
                    } while ($this->hasInternalAlias($publicUrl));

                    $alias = new UrlAlias($publicUrl, $internalUrl, $type);
                    $this->save($alias);
                    $ret = true;
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid argument exception");
            }
        } else {
            $alias = new UrlAlias($publicUrl, $internalUrl, $type);
            $this->save($alias);
            $ret = true;
        }
        return $ret;
    }

    /**
     * Changes the $publicUrl of an UrlAlias to $newPublicUrl
     * Adds a new UrlAlias with $type and point it to $newPublicUrl
     *
     * Given an existing UrlAlias pointing:
     *      A -> B
     * After this method has been run
     *      A -> C and C -> B
     * Where A -> C is a new UrlAlias of the type $type
     * Where C -> B is an existing UrlAlias with the publicUrl changed
     *
     * @param string $newPublicUrl The new public url to move to the alias to.
     * @param string $publicUrl    The current public url of the UrlAlias we're moving.
     * @param string $internalUrl  The current internal url of the UrlAlias we're moving
     * @param integer $type        The type of move we want to make. a.k.a. "mode"
     * @return boolean Wheter the move action was successful.
     */
    public function moveAlias($newPublicUrl, $publicUrl, $internalUrl, $type = UrlAlias::ALIAS)
    {
        $moved = false;
        if ($newPublicUrl === $publicUrl) {
            return $moved;
        }
        /** @var UrlAlias $existingAlias */
        $existingAlias = $this->findAlias($publicUrl, $internalUrl);
        $newAliasExists = $this->hasInternalAlias($newPublicUrl, true);
        // if the old alias exists, and the new one doesn't
        if (!is_null($existingAlias) && is_null($newAliasExists)) {

            // change the old alias
            $existingAlias->setPublicUrl($newPublicUrl);
            // create a new one
            $newAlias = new UrlAlias();
            $newAlias->setPublicUrl($publicUrl);
            $newAlias->setInternalUrl($newPublicUrl);
            $newAlias->setMode($type);

            $this->save($existingAlias);
            $this->save($newAlias);
            $moved = true;
        }
        return $moved;
    }


    /**
     * Set the batch to 'true' if aliases are being batch processed (optimization).
     *
     * This method returns a callback that needs to be executed after the batch is done; this is up to the caller.
     *
     * @param bool $isBatch
     * @return callable
     */
    public function setIsBatch($isBatch)
    {
        $this->batch = array();
        $this->isBatch = $isBatch;
        $mgr = $this->manager;
        $self = $this;
        return function() use($mgr, $self) {
            $mgr->flush();
            $self->setIsBatch(true);
        };
    }


    /**
     * Persist the URL alias.
     *
     * @param \Zicht\Bundle\UrlBundle\Entity\UrlAlias $alias
     * @return void
     */
    protected function save(UrlAlias $alias)
    {
        $this->manager->persist($alias);

        if ($this->isBatch) {
            $this->batch[$alias->getPublicUrl()]= $alias;
        } else {
            $this->manager->flush($alias);
        }
    }


    /**
     * Compact redirects; i.e. optimize redirects:
     *
     * If /a points to /b, and /b points to /c, let /a point to /c
     *
     * @return void
     */
    public function compact()
    {
        foreach ($this->getRepository()->findAll() as $urlAlias) {
            if ($cascadingAlias = $this->hasPublicAlias($urlAlias->internal_url)) {
                $urlAlias->setInternalUrl($cascadingAlias->getInternalUrl());
            }
        }
    }


    /**
     * Remove alias
     *
     * @param string $internalUrl
     * @return void
     */
    public function removeAlias($internalUrl)
    {
        if ($alias = $this->hasPublicAlias($internalUrl, true)) {
            $this->manager->remove($alias);
            $this->manager->flush($alias);
        }
    }

    /**
     * Takes HTML text and replaces all <a href='INTERNAL'> into <a href='PUBLIC'>.
     *
     * @param string $html
     * @return string
     */
    public function internalToPublicHtml($html)
    {
        return $this->processAliasingInHtml($html, 'internal-to-public');
    }

    /**
     * Takes HTML text and replaces all <a href='PUBLIC'> into <a href='INTERNAL'>.
     *
     * @param string $html
     * @return string
     */
    public function publicToInternalHtml($html)
    {
        return $this->processAliasingInHtml($html, 'public-to-internal');
    }

    /**
     * Helper function doing the actual work behind internalToPublicHtml and publicToInternalHtml
     *
     * @param string $html
     * @param string $mode Can be either 'internal-to-public' or 'public-to-internal'
     * @return string
     */
    private function processAliasingInHtml($html, $mode)
    {
        // 'ref' in the regex is no typo here. A look-back assertion must be of fixed length, so this is a minor
        // optimization.
        if (!preg_match_all('/(?<=(?:ref|src)=")([^"]+)/', $html, $m)) {
            // early return: if there are no matches, no need for the rest of the processing.
            return $html;
        }

        // sorting the items first will make the 'in_array' further down more efficient.
        sort($m[1]);

        $urls = array();
        foreach ($m[1] as $url) {
            // exclusion (may need to configure these in the future?)
            if (
                   0 === strpos($url, '/bundles/')
                || 0 === strpos($url, '/media/')
                || 0 === strpos($url, '/js/')
                || 0 === strpos($url, '/style/')
                || 0 === strpos($url, '/favicon.ico')
                || 0 === strpos($url, '#')
                || 0 === strpos($url, 'mailto:')
                || 0 === strpos($url, 'tel:')
                || 0 === strpos($url, 'http:')
                || 0 === strpos($url, 'https:')
            ) {
                continue;
            }

            if (!in_array($url, $urls)) {
                $urls[]= $url;
            }
        }

        if (count($urls)) {
            return strtr($html, $this->getAliasingMap($urls, $mode));
        }
        return $html;
    }


    public function getAliasingMap($urls, $mode)
    {
        switch ($mode) {
            case 'internal-to-public':
                $from = 'internal_url';
                $to = 'public_url';
                break;
            case 'public-to-internal':
                $from = 'public_url';
                $to = 'internal_url';
                break;
            default:
                throw new InvalidArgumentException("Invalid mode supplied: {$mode}");
        }

        $connection = $this->manager->getConnection()->getWrappedConnection();

        $sql = sprintf(
            'SELECT %1$s, %2$s FROM url_alias WHERE mode=%3$d AND %1$s IN(%4$s)',
            $from,
            $to,
            UrlAlias::REWRITE,
            join(
                ', ',
                array_map(
                    function($v) use($connection) {
                        return $connection->quote($v, \PDO::PARAM_STR);
                    },
                    $urls
                )
            )
        );

        if ($stmt = $connection->query($sql)) {
            return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        }
        return array();
    }
}