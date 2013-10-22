<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Entity;

use \Doctrine\Common\Collections\ArrayCollection;
use \Doctrine\ORM\Mapping as ORM;

/**
 * StaticReference
 *
 * @ORM\Table(name="static_reference")
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\Entity
 */
class StaticReference
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="machine_name", type="string", length=255)
     */
    private $machine_name;

    /**
     * @var StaticReferenceTranslation[]
     * @ORM\OneToMany(
     *     targetEntity="Zicht\Bundle\UrlBundle\Entity\StaticReferenceTranslation",
     *     mappedBy="static_reference",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     */
    public $translations;

    /**
     * Default construction of entity
     *
     * @return \Zicht\Bundle\UrlBundle\Entity\StaticReference
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set machine_name
     *
     * @param string $machineName
     *
     * @return StaticReference
     */
    public function setMachineName($machineName)
    {
        $this->machine_name = $machineName;

        return $this;
    }

    /**
     * Get machine_name
     *
     * @return string
     */
    public function getMachineName()
    {
        return $this->machine_name;
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function getTranslation($locale)
    {
        return $this->hasTranslation($locale);
    }

    /**
     * Setter for translations
     *
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
     * @param StaticReferenceTranslation $translation
     * @return void
     */
    public function addTranslations(StaticReferenceTranslation $translation)
    {
        $translation->setStaticReference($this);

        $this->translations[] = $translation;
    }
}
