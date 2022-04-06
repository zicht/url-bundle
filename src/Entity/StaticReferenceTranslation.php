<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="static_reference_translation")
 * @ORM\Entity
 */
class StaticReferenceTranslation
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var StaticReference|null
     * @ORM\ManyToOne(targetEntity="Zicht\Bundle\UrlBundle\Entity\StaticReference", inversedBy="translations")
     */
    public $static_reference;

    /**
     * @var string
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var string
     * @ORM\Column(name="locale", type="string", length=6, nullable=true)
     */
    private $locale;

    /**
     * Create a translation
     *
     * @param string $locale
     * @param string $url
     */
    public function __construct($locale = null, $url = null)
    {
        $this->locale = $locale;
        $this->url    = $url;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = strtolower(trim($locale));
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $static_reference
     * @return void
     */
    public function setStaticReference($static_reference)
    {
        $this->static_reference = $static_reference;
    }

    /**
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
