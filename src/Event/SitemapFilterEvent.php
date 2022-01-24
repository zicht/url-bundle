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

    /**
     * @param \ArrayObject $object
     */
    public function __construct(\ArrayObject $object)
    {
        $this->object = $object;
    }

    /**
     * {@inheritDoc}
     */
    public function getArrayCopy()
    {
        return $this->object->getArrayCopy();
    }

    /**
     * @param callable $filter
     */
    public function filter(callable $filter)
    {
        foreach ($this->getArrayCopy() as $key => $value) {
            if (false === $filter($key, $value)) {
                $this->offsetUnset($key);
            }
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function exchange(array $data)
    {
        return $this->object->exchangeArray($data);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return $this->object->offsetExists($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->object->offsetGet($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->object->offsetSet($offset, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        $this->object->offsetUnset($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        foreach ($this->object as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return $this->object->count();
    }
}
