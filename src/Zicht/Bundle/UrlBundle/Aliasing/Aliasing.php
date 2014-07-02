<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use \Doctrine\Bundle\DoctrineBundle\Registry;
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
        if ($alias = $this->getRepository()->findOneBy($params)) {
            $ret = ($asObject ? $alias : $alias->getPublicUrl());
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
            $this->manager->flush();
        }
    }
}