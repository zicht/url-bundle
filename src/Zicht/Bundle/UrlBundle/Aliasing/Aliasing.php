<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use Doctrine\Bundle\DoctrineBundle\Registry;
use \Zicht\Bundle\UrlBundle\Entity\UrlAlias;

class Aliasing
{
    const STRATEGY_OVERWRITE    = 'overwrite';
    const STRATEGY_KEEP         = 'keep';
    const STRATEGY_SUFFIX       = 'suffix';

    protected $isBatch = false;

    function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->batch = array();
    }


    function hasInternalAlias($publicUrl, $asObject = false, $mode = null)
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


    function hasPublicAlias($internalUrl, $asObject = false)
    {
        $ret = null;

        if ($alias = $this->getRepository()->findOneBy(array('internal_url' => $internalUrl, 'mode' => UrlAlias::REWRITE))) {
            $ret = ($asObject ? $alias : $alias->getPublicUrl());
        }

        return $ret;
    }


    public function getRepository()
    {
        return $this->doctrine->getManager()->getRepository('ZichtUrlBundle:UrlAlias');
    }


    public function addAlias($src, $target, $type, $strategy = self::STRATEGY_OVERWRITE)
    {
        $ret = false;
        /** @var $alias UrlAlias */

        if ($alias = $this->hasInternalAlias($src, true)) {
            switch ($strategy) {
                case self::STRATEGY_OVERWRITE:
                    $alias->setInternalUrl($target);
                    $this->save($alias);
                    $ret = true;
                    break;
                case self::STRATEGY_KEEP:
                    // do nothing intentionally
                    break;
                case self::STRATEGY_SUFFIX:
                    $original = $src;
                    $i = 1;
                    do {
                        $src = $original . '-' . ($i ++);
                    } while ($this->hasInternalAlias($src));

                    $alias = new UrlAlias($src, $target, $type);
                    $this->save($alias);
                    $ret = true;
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid argument exception");
            }
        } else {
            $alias = new UrlAlias($src, $target, $type);
            $this->save($alias);
            $ret = true;
        }
        return $ret;
    }

    public function setIsBatch($isBatch)
    {
        $this->batch = array();
        $this->isBatch = $isBatch;
        $mgr = $this->doctrine->getManager();
        $self = $this;
        return function() use($mgr, $self) {
            $mgr->flush();
            $self->setIsBatch(true);
        };
    }

    protected function save(UrlAlias $alias)
    {
        $this->doctrine->getManager()->persist($alias);

        if ($this->isBatch) {
            $this->batch[$alias->getPublicUrl()]= $alias;
        } else {
            $this->doctrine->getManager()->flush($alias);
        }
    }

    public function compact()
    {
        foreach ($this->getRepository()->findAll() as $urlAlias) {
            if ($cascadingAlias = $this->hasPublicAlias($urlAlias->internal_url)) {
                $urlAlias->setInternalUrl($cascadingAlias->getInternalUrl());
            }
        }
    }
}