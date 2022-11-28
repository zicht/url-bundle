<?php declare(strict_types=1);

namespace Zicht\Bundle\UrlBundle\Type;

use Symfony\Component\Form\DataTransformerInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

class UrlTypeAutocompleteDataTransformer implements DataTransformerInterface
{
    private Aliasing $aliasing;

    public function __construct(Aliasing $aliasing)
    {
        $this->aliasing = $aliasing;
    }

    public function transform($value)
    {
        if (null === $value) {
            return $value;
        }
        if (!$alias = $this->aliasing->getRepository()->findOneByPublicUrl($value)) {
            return null;
        }
        // Return values that for AutocompleteType understands
        return [
            'label' => $alias->getPublicUrl(),
            'value' => $alias->getPublicUrl(),
            'id' => $alias->getId(),
        ];
    }

    public function reverseTransform($value)
    {
        if (\is_numeric($value)) {
            $alias = $this->aliasing->getRepository()->find((int)$value);
            return $alias->getPublicUrl();
        }
        return $value;
    }
}
