<?php
/**
 * @author Philip Bergman <philip@zicht.nl>
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Listener;

use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * Class StrictListener
 *
 * A class metadata listener that will set strict rulings on the UrlAlias entity.
 *
 * @package Zicht\Bundle\UrlBundle\Listener
 */
class StrictListener
{
    protected $isStrict = false;

    /**
     * StrictListener constructor
     *
     * @param bool $strict
     */
    public function __construct($strict = false)
    {
        $this->isStrict = $strict;
    }

    /**
     * @{inheritDoc}
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args)
    {
        if (false === $this->isStrict) {
            return;
        }

        /** @var \Doctrine\ORM\Mapping\ClassMetadata $classMetadata */
        $classMetadata = $args->getClassMetadata();

        if (UrlAlias::class === $classMetadata->getName()) {
            $classMetadata->fieldMappings['public_url']['unique'] = true;
        }
    }
}
