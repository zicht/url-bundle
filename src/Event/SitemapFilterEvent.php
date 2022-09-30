<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class SitemapFilterEvent extends Event implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /** @var \ArrayObject */
    protected $object;

    public function __construct(\ArrayObject $object)
    {
        $this->object = $object;
    }

    public function getArrayCopy()
    {
        return $this->object->getArrayCopy();
    }

    public function filter(callable $filter)
    {
        foreach ($this->getArrayCopy() as $key => $value) {
            if (false === $filter($key, $value)) {
                $this->offsetUnset($key);
            }
        }
    }

    /**
     * @return array
     */
    public function exchange(array $data)
    {
        return $this->object->exchangeArray($data);
    }

    public function offsetExists($offset)
    {
        return $this->object->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->object->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->object->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->object->offsetUnset($offset);
    }

    public function getIterator()
    {
        foreach ($this->object as $key => $value) {
            yield $key => $value;
        }
    }

    public function count()
    {
        return $this->object->count();
    }
}
