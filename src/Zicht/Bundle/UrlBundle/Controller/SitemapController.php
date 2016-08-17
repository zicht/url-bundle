<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

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
     * @return Response
     *
     * @Route("/sitemap.{_format}", defaults={"_format": "xml"})
     */
    public function sitemapAction()
    {
        $urls = $this->get('zicht_url.sitemap_provider')->all($this->get('security.authorization_checker'));

        return new Response(
            $this->renderView('ZichtUrlBundle:Sitemap:sitemap.xml.twig', array('urls' => $urls)),
            200,
            array(
                'content-type' => 'text/xml'
            )
        );
    }
}
