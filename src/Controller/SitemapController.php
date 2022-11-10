<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zicht\Bundle\UrlBundle\Url\SitemapProvider;

/**
 * @final
 */
class SitemapController extends AbstractController
{
    public static function getSubscribedServices()
    {
        return [SitemapProvider::class] + parent::getSubscribedServices();
    }

    /**
     * Render basic sitemap from all database urls
     *
     * @return Response
     * @Route("/sitemap.{_format}", defaults={"_format": "xml"})
     */
    public function sitemapAction(Request $request)
    {
        $urls = $this->get(SitemapProvider::class)->all($this->get('security.authorization_checker'));

        return new Response(
            $this->renderView('@ZichtUrl/Sitemap/sitemap.xml.twig', ['urls' => $urls]),
            200,
            [
                'content-type' => 'text/xml',
            ]
        );
    }
}
