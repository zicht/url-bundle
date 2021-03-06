<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url\Params;

use Zicht\Bundle\FrameworkExtraBundle\Util\SortedSetMap;

/**
 * Abstracts faceted uri's into a Map construct, with each key in the map corresponding to the name of the
 * facet and each facet represented as a sorted set.
 *
 * The with() method will return a clone of the instance with a value for the given key added
 * or removed. It is added if it wasn't yet in the set, it is removed if it was.
 *
 * Usage:
 *
 *      $uri = new Zicht_Util_FacetUri('a=1/b=2/c=3,4');
 *
 *      $uri->with('a', 2); // will return a new instance with "a=1,2/b=2/c=3,4"
 *      $uri->with('c', 3); // will return a new instance with "a=1/b=2/c=4"
 */
class Params extends SortedSetMap
{
    /**
     * @var UriParser
     */
    protected $parser;

    /**
     * Construct the object and parse the URI.
     *
     * Each of the separator parameters is used for providing different formats of the URI. These separators
     * will be used to generate a string of the object too.
     *
     * @param null|UriParser $parser
     * @return Params
     */
    public function __construct(UriParser $parser = null)
    {
        parent::__construct();
        if (is_null($parser)) {
            $parser = new UriParser();
        }
        $this->parser = $parser;
    }


    /**
     * Parses the uri and assigns the parsed values.
     *
     * @param string $uri
     * @return void
     */
    public function setUri($uri)
    {
        $this->setValues($this->parser->parseUri($uri));
    }


    /**
     * Process the POST and merges the (translated) values.
     *
     * @param array $post
     * @return void
     */
    public function mergePost($post)
    {
        $this->mergeAll($this->parser->parsePost($post));
    }


    /**
     * Duplicates the current instance with one value changed.
     *
     * - If the value already exists, the value is removed;
     * - If the value does not exist, it is added;
     * - If the value exists, and 'multiple' is false, it is replaced.
     * - If the value does not exists, and 'multiple' is false, it is added.
     *
     * The $multiple parameter is typically useful for paging or other parameters.
     *
     * @param string $key
     * @param string $value
     * @param bool $multiple
     * @return Params
     */
    public function with($key, $value, $multiple = true)
    {
        $ret = clone $this;
        $ret = self::doWith($ret, $key, $value, $multiple);

        return $ret;
    }


    /**
     * Helper for with()
     *
     * @param Params $ret
     * @param string $key
     * @param string $value
     * @param bool $multiple
     * @return Params
     */
    private static function doWith(Params $ret, $key, $value, $multiple = true)
    {
        if (!$multiple) {
            if (!is_scalar($value)) {
                throw new \InvalidArgumentException(
                    'Invalid argument $value to with(), expected scalar, got ' . gettype($value)
                );
            }
            if ($ret->contains($key, $value)) {
                $ret->remove($key, $value);
            } else {
                $ret->replace($key, [$value]);
            }
        } else {
            if ($ret->contains($key, $value)) {
                $ret->remove($key, $value);
            } else {
                $ret->add($key, $value);
            }
        }

        return $ret;
    }


    /**
     * Returns a single value, and defaults to the given default parameter if not available.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOne($key, $default = null)
    {
        $ret = $default;
        $all = $this->get($key);
        if (count($all) > 0) {
            $ret = array_shift($all);
        }

        return $ret;
    }


    /**
     * Duplicates the current instance with one or more sets of values removed
     *
     * @param mixed $keys
     * @return Params
     */
    public function without($keys)
    {
        if (is_scalar($keys)) {
            $keys = [$keys];
        }
        $ret = clone $this;
        foreach ($keys as $key) {
            $ret->removeKey($key);
        }

        return $ret;
    }


    /**
     * Returns all keys in the current set.
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->toArray());
    }

    /**
     * Renders a string of the URI, joining it using the separators provided at construction time.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->parser->composeUri($this->toArray());
    }
}
