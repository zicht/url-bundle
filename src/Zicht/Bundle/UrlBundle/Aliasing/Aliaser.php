<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;
use Zicht\Bundle\UrlBundle\Aliasing\DefaultAliasingStrategy;
use Zicht\Bundle\UrlBundle\Aliasing\AliasingStrategy;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Url\Provider;
use Zicht\Util\Str;

class Aliaser
{
    function __construct(Aliasing $aliasing, Provider $provider, AliasingStrategy $naming = null)
    {
        $this->aliasing = $aliasing;
        $this->provider = $provider;
        if (null === $naming) {
            $naming = new DefaultAliasingStrategy();
        }
        $this->aliasingStrategy = $naming;
    }


    function createAlias($record)
    {
        $internalUrl = $this->provider->url($record);
        if (!$this->aliasing->hasPublicAlias($internalUrl)) {
            return $this->aliasing->addAlias(
                $this->aliasingStrategy->generatePublicAlias($record),
                $internalUrl,
                UrlAlias::REWRITE,
                Aliasing::STRATEGY_SUFFIX
            );
        }
        return false;
    }
}