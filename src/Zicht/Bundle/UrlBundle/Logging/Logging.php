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
        $log = new ErrorLog();

        $log->message   = $message;
        $log->date_created = new \DateTime();
        $log->referer   = $request->headers->get('referer', null);
        $log->ua        = $request->headers->get('user-agent', null);
        $log->ip        = $request->getClientIp();
        $log->url       = $request->getRequestUri();

        return $log;
    }


    function flush($entry)
    {
        $mgr = $this->doctrine->getManager();
        $mgr->persist($entry);
        $mgr->flush($entry);
    }
}