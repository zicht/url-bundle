<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

$o = new \ArrayObject();
$o['a'] = ['b', 'c'];
$o['d'] = ['e', 'f'];
$o['g'] = ['h', 'i'];

foreach ($o as $key => $value) {
    if ($key === 'b') {
        $o->offsetUnset($key);
    }
}

var_dump($o);exit;