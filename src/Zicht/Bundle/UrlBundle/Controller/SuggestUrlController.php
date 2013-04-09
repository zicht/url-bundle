<?php
/**
 * For licensing information, please see the LICENSE file accompanied with this file.
 *
 * @author Gerard van Helden <drm@melp.nl>
 * @copyright 2012 Gerard van Helden <http://melp.nl>
 */

namespace Zicht\Bundle\UrlBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class SuggestUrlController extends Controller
{
    /**
     * @Route("/url/suggest")
     */
    function suggestUrlAction(Request $request)
    {
        return new JsonResponse(
            array(
                'suggestions' => $this->get('zicht_url.provider')->suggest($request->get('pattern'))
            )
        );
    }
}