<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *  name = "url_alias",
 *  indexes={
 *      @ORM\Index(name="public_url_idx", columns={"public_url"}),
 *      @ORM\Index(name="internal_url_idx", columns={"internal_url", "mode"})
 * })
 */
class UrlAlias
{
    /**
     * The alias is an internal rewrite, i.e. external url's are rewritten to internal on request,
     * and vice versa when composing an url.
     */
    const REWRITE   = 0;

    /**
     * The MOVE type yields a 301 response with the internal url if the public url is matched
     */
    const MOVE      = 301;

    /**
     * The ALIAS type yields a 302 response with the internal url if the public url is matched
     */
    const ALIAS     = 302;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type = "integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $public_url;

    /**
     * @ORM\Column(type="string")
     */
    protected $internal_url;

    /**
     * @ORM\Column(type="integer")
     */
    protected $mode = self::REWRITE;


    function __construct($public_url = null, $internal_url = null, $mode = null)
    {
        $this->public_url = $public_url;
        $this->internal_url = $internal_url;
        $this->mode = $mode;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setInternalUrl($internal_url)
    {
        $this->internal_url = $internal_url;
    }

    public function getInternalUrl()
    {
        return $this->internal_url;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function setPublicUrl($public_url)
    {
        $this->public_url = $public_url;
    }

    public function getPublicUrl()
    {
        return $this->public_url;
    }

    function __toString()
    {
        return (string)$this->id;
    }

}