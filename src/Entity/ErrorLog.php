<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log entries
 *
 * @ORM\Entity
 * @ORM\Table(name="url_error_log")
 */
class ErrorLog
{
    /**
     * @var int
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $status;

    /**
     * @var string
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    protected $url;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $ip;

    /**
     * @var string
     * @ORM\Column(type="string", length=1024)
     */
    protected $ua;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $message;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $referer;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date_created;

    /**
     * Create the log entry
     *
     * @param string $message
     * @param \DateTime $date_created
     * @param string $referer
     * @param string $ua
     * @param string $ip
     * @param string $url
     */
    public function __construct($message, $date_created, $referer, $ua, $ip, $url)
    {
        $this
            ->setMessage($message)
            ->setDateCreated($date_created)
            ->setReferer($referer)
            ->setUa($ua)
            ->setIp($ip)
            ->setUrl($url);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->message
            . ' @ ' . (string)($this->date_created ? $this->date_created->format('YmdHis') : '');
    }

    /**
     * @param \DateTime $date_created
     * @return ErrorLog
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param string $ip
     * @return self
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $message
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $referer
     * @return self
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReferer()
    {
        return $this->referer;
    }

    /**
     * @param int $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $ua
     * @return self
     */
    public function setUa($ua)
    {
        $this->ua = $ua;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUa()
    {
        return $this->ua;
    }

    /**
     * @param string $url
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }
}
