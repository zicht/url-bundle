<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SitemapController
 *
 * @package Zicht\Bundle\UrlBundle\Controller
 */
class SitemapController extends Controller
{
    /**
     * Render basic sitemap from all database urls
     *
     * @Route("/sitemap.{_format}", defaults={"_format": "xml"})
     */
    public function sitemapAction()
    {
        $response = $this->render(
            'ZichtUrlBundle:Sitemap:sitemap.xml.twig',
            [
                'urls' => $this->get('zicht_url.sitemap_provider')->all($this->get('security.context'))
            ]
        );
        $response->headers->set('Content-Type', 'text/xml');
        return $response;
    }
}