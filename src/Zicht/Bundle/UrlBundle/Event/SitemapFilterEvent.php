<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Event;

use Symfony\Component\EventDispatcher\GenericEvent;
use Zicht\Itertools\lib\Interfaces\FiniteIterableInterface;

/**
 * Class SitemapFilterEvent
 */
class SitemapFilterEvent extends GenericEvent
{
    /**
     * Expose filter functionality in a way so the result is automatically applied to the collection.
     *
     * @param callable $filter
     * @return mixed|null|void|\Zicht\Itertools\lib\FilterIterator
     */
    public function filterSitemapUrls(callable $filter)
    {
        if (!$this->subject instanceof FiniteIterableInterface) {
            return;
        }

        return $this->subject = $this->subject->filter($filter);
    }
}
