<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Bundle\UrlBundle\Controller;

use \Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use \Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\Request;

/**
 * Mounted on the admin path for security.
 *
 * @Route("/admin")
 */
class SuggestUrlController extends Controller
{
    /**
     * Controller used for url suggestions by the url provider.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/url/suggest")
     */
    public function suggestUrlAction(Request $request)
    {
        return new JsonResponse(
            array(
                'suggestions' => $this->get('zicht_url.provider')->suggest($request->get('pattern'))
            )
        );
    }

    /**
     * @Route("/url/suggest/editor")
     */
    public function linkListAction(Request $request)
    {
        return new JsonResponse(
            $this->get('zicht_url.provider')->all()
        );
    }
}