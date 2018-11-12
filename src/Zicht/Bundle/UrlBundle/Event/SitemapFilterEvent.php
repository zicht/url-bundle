<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class SitemapFilterEvent.
 */
class SitemapFilterEvent extends Event implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /** @var \ArrayObject */
    protected $object;

    /**
     * SitemapFilterEvent constructor.
     *
     * @param \ArrayObject $object
     */
    public function __construct(\ArrayObject $object)
    {
        $this->object = $object;
    }

    /**
     * @inheritdoc
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
        foreach ($this->object as $key => $value) {
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
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->object->offsetExists($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->object->offsetGet($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->object->offsetSet($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->object->offsetUnset($offset);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        foreach ($this->object as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->object->count();
    }
}
