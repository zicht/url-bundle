<?php

namespace Zicht\Bundle\UrlBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zicht\Bundle\UrlBundle\Entity\Repository\StaticReferenceRepository;

#[ORM\Entity(repositoryClass: StaticReferenceRepository::class)]
#[ORM\Table(name: 'static_reference')]
class StaticReference
{
    /** @var int */
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private $id;

    /** @var string */
    #[ORM\Column(type: 'string')]
    private $machine_name;

    /** @var StaticReferenceTranslation[] */
    #[ORM\OneToMany(targetEntity: StaticReferenceTranslation::class, mappedBy: 'static_reference', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public $translations;

    /**
     * Default construction of entity
     *
     * @param string $machineName
     * @param array $translations
     */
    public function __construct($machineName = null, $translations = null)
    {
        $this->translations = new ArrayCollection();
        if (null !== $machineName) {
            $this->setMachineName($machineName);
        }

        if (null !== $translations) {
            foreach ($translations as $language => $url) {
                $this->addTranslations(new StaticReferenceTranslation($language, $url));
            }
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $machineName
     * @return StaticReference
     */
    public function setMachineName($machineName)
    {
        $this->machine_name = $machineName;

        return $this;
    }

    /**
     * @return string
     */
    public function getMachineName()
    {
        return $this->machine_name;
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function getTranslation($locale)
    {
        return $this->hasTranslation($locale);
    }

    /**
     * @param mixed $translations
     * @return void
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!empty($this->machine_name)) {
            return $this->machine_name;
        } else {
            return (string)$this->id;
        }
    }

    /**
     * Checks if a translation is set for the given locale
     *
     * @param string $locale
     * @return bool
     */
    public function hasTranslation($locale)
    {
        foreach ($this->translations as $translation) {
            if ($locale == $translation->getLocale()) {
                return $translation;
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Set translations that are not yet initialized
     *
     * @return void
     */
    public function addMissingTranslations()
    {
        foreach ($this->translations as $translation) {
            $translation->setStaticReference($this);
        }
    }

    /**
     * Add a translation
     *
     * @return void
     */
    public function addTranslations(StaticReferenceTranslation $translation)
    {
        $translation->setStaticReference($this);

        $this->translations[] = $translation;
    }
}
