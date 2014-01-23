<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Controller;

use \Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Utility controller to reference a static ref from outside the system.
 */
class StaticReferenceController extends Controller
{
    /**
     * Redirects to the url provided by the main url provider service.
     *
     * @param string $name
     * @param int $code
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route("/_static-ref/{name}")
     */
    public function redirectAction($name, $code = 301)
    {

        return new RedirectResponse(
            $this->get('zicht_url.provider')->url($name),
            $code
        );
    }
}