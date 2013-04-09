<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Controller;

use \Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
 
class StaticReferenceController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    /**
     * @Route("/_static-ref/{name}")
     *
     * @param $name
     * @param int $code
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    function redirectAction($name, $code = 301) {
        return new RedirectResponse(
            $this->get('zicht_url.provider')->url($name),
            $code
        );
    }
}