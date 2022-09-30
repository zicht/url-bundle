<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Bundle\UrlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Mounted on the admin path for security.
 *
 * @final
 * @Route("/admin")
 */
class SuggestUrlController extends AbstractController
{
    /**
     * Controller used for url suggestions by the url provider.
     *
     * @return Response
     *
     * @Route("/url/suggest")
     */
    public function suggestUrlAction(Request $request)
    {
        return new JsonResponse(
            [
                'suggestions' => $this->get('zicht_url.provider')->suggest($request->get('pattern')),
            ]
        );
    }

    /**
     * Lists all links available in the url provider.
     *
     * @return Response
     *
     * @Route("/url/suggest/editor")
     */
    public function linkListAction(Request $request)
    {
        return new JsonResponse(
            $this->get('zicht_url.provider')->all($this->get('security.authorization_checker'))
        );
    }
}
