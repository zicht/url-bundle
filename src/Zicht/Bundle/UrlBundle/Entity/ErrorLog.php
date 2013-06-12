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
    protected $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $ip;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    protected $ua;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $message;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $referer;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date_created;


    public function __construct($message, $date_created, $referer, $ua, $ip, $url)
    {
        $this
            ->setMessage($message)
            ->setDateCreated($date_created)
            ->setReferer($referer)
            ->setUa($ua)
            ->setIp($ip)
            ->setUrl($url)
        ;
    }



    public function getId()
    {
        return $this->id;
    }


    function __toString()
    {
        return (string) $this->message . ' @ ' . (string) ($this->date_created ? $this->date_created->format('YmdHis') : '');
    }

    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
        return $this;
    }

    public function getDateCreated()
    {
        return $this->date_created;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    public function getIp()
    {
        return $this->ip;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setReferer($referer)
    {
        $this->referer = $referer;
        return $this;
    }

    public function getReferer()
    {
        return $this->referer;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setUa($ua)
    {
        $this->ua = $ua;
        return $this;
    }

    public function getUa()
    {
        return $this->ua;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }
}