<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Utility controller to reference a static ref from outside the system.
 *
 * @final
 */
class StaticReferenceController extends AbstractController
{
    /**
     * Redirects to the url provided by the main url provider service.
     *
     * @param string $name
     * @param int $code
     * @return RedirectResponse
     *
     * @Route("/_static-ref/{name}")
     */
    public function redirectAction(Request $request, $name, $code = 301)
    {
        return new RedirectResponse(
            $this->get('zicht_url.provider')->url($name),
            $code
        );
    }
}
