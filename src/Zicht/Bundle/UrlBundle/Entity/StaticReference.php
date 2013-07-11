<?php

namespace Zicht\Bundle\UrlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=6, nullable=true)
     */
    private $language;

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
     * Set url
     *
     * @param string $url
     * @return StaticReference
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    function __toString()
    {
        if (!empty($this->machine_name)) {
            return $this->machine_name;
        } else {
            return (string) $this->id;
        }
    }
}
