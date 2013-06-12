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
    function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;

    }

    function hasInternalAlias($url, $asObject = false)
    {
        $ret = null;

        if ($alias = $this->getRepository()->findOneBy(array('public_url' => $url))) {
            $ret = ($asObject ? $alias : $alias->getInternalUrl());
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


    public function getRepository()
    {
        return $this->doctrine->getManager()->getRepository('ZichtUrlBundle:UrlAlias');
    }


    public function addAlias($src, $target, $type)
    {
        $alias = new UrlAlias($src, $target, $type);
        $this->doctrine->getManager()->persist($alias);
        $this->doctrine->getManager()->flush();
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