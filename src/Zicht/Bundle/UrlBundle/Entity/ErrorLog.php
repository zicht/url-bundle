<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="url_error_log")
 */
class ErrorLog
{
    /**
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    public $status;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    public $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    public $ip;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    public $ua;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $message;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public $referer;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $date_created;


    public function getId()
    {
        return $this->id;
    }


    function __toString()
    {
        return (string) $this->message . ' @ ' . (string) ($this->date_created ? $this->date_created->format('YmdHis') : '');
    }
}