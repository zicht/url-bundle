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
        $pattern = $request->get('pattern');

        $suggestions = $this->get('zicht_url.provider')->suggest($pattern);

        usort($suggestions, function($a, $b) use ($pattern) {
            $percentA = 0;
            $percentB = 0;
            similar_text($a['label'], $pattern, $percentA);
            similar_text($b['label'], $pattern, $percentB);

            return $percentB - $percentA;
        });

        $suggestions = array_slice($suggestions, 0, 15);

        return new JsonResponse(
            array(
                'suggestions' => $suggestions
            )
        );
    }

    /**
     * @Route("/url/suggest/editor")
     */
    public function linkListAction(Request $request)
    {
        return new JsonResponse(
            $this->get('zicht_url.provider')->all($this->get('security.context'))
        );
    }
}
