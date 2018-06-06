<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class SitemapQueryEvent
 */
class SitemapQueryEvent extends GenericEvent
{
    private $queryArguments;

    /**
     * @return mixed
     */
    public function getQueryArguments()
    {
        return $this->queryArguments;
    }

    /**
     * @param mixed $queryArguments
     * @return void
     */
    public function setQueryArguments($queryArguments)
    {
        $this->queryArguments = $queryArguments;
    }
}
