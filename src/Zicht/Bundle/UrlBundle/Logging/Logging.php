<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Logging;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Zicht\Bundle\UrlBundle\Entity\ErrorLog;

class Logging
{
    function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }


    function createLog(Request $request, $message)
    {
        return new ErrorLog(
            $message,
            new \DateTime(),
            $request->headers->get('referer', null),
            $request->headers->get('user-agent', null),
            $request->getClientIp(),
            $request->getRequestUri()
        );
    }


    function flush($entry)
    {
        $mgr = $this->doctrine->getManager();
        $mgr->persist($entry);
        $mgr->flush($entry);
    }
}