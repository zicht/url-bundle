<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
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
     * @return array
     *
     * @Route("/sitemap.{_format}", defaults={"_format": "xml"})
     */
    public function sitemapAction()
    {
        $urls = $this->get('zicht_url.sitemap_provider')->all($this->get('security.context'));
        $content = $this->renderView('ZichtUrlBundle:Sitemap:sitemap.xml.twig', array('urls' => $urls));
        return new Response(
            $content,
            200,
            array('Content-Type' => 'text/xml')
        );
    }
}