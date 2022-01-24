<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @final
 */
class SitemapController extends AbstractController
{
    /**
     * Render basic sitemap from all database urls
     *
     * @param Request $request
     * @return Response
     *
     * @Route("/sitemap.{_format}", defaults={"_format": "xml"})
     */
    public function sitemapAction(Request $request)
    {
        $urls = $this->get('zicht_url.sitemap_provider')->all($this->get('security.authorization_checker'));

        return new Response(
            $this->renderView('@ZichtUrl/Sitemap/sitemap.xml.twig', ['urls' => $urls]),
            200,
            [
                'content-type' => 'text/xml',
            ]
        );
    }
}
