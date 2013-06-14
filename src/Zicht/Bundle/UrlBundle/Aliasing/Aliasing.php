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

    function __construct(Registry $doctrine, $excludePatterns = array())
    {
        $this->doctrine = $doctrine;
        $this->excludePatterns = $excludePatterns;
    }


    function hasInternalAlias($url, $asObject = false)
    {
        $ret = null;

        if (!$this->isExcluded($url)) {
            if ($alias = $this->getRepository()->findOneBy(array('public_url' => $url))) {
                $ret = ($asObject ? $alias : $alias->getInternalUrl());
            }
        }

        return $ret;
    }


    function hasPublicAlias($url, $asObject = false)
    {
        $ret = null;

        if ($alias = $this->getRepository()->findOneBy(array('internal_url' => $url, 'mode' => UrlAlias::REWRITE))) {
            $ret = ($asObject ? $alias : $alias->getPublicUrl());
        }

        return $ret;
    }


    function isExcluded($url)
    {
        $ret = false;
        foreach ($this->excludePatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                $ret = true;
                break;
            }
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
        $mgr = $this->doctrine->getManager();

        if ($alias = $this->hasPublicAlias($src, true)) {
            switch ($strategy) {
                case self::STRATEGY_OVERWRITE:
                    $alias->setInternalUrl($target);
                    $mgr->persist($alias);
                    $mgr->flush($alias);
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
                    } while ($this->hasPublicAlias($src));

                    $alias = new UrlAlias($src, $target, $type);
                    $mgr->persist($alias);
                    $mgr->flush();
                    $ret = true;
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid argument exception");
            }
        } else {
            $alias = new UrlAlias($src, $target, $type);
            $mgr->persist($alias);
            $mgr->flush($alias);
            $ret = true;
        }
        return $ret;
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