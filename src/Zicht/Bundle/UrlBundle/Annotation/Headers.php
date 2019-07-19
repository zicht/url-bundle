<?php
declare(strict_types=1);
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Headers
{
    /**
     * @var array
     */
    public $headers;

    public function __construct(array $values)
    {
        $this->headers = $values['headers'] ?? [];
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
