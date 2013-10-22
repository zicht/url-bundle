<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Logging;

use \Doctrine\Bundle\DoctrineBundle\Registry;
use \Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\HttpFoundation\Request;
use \Zicht\Bundle\UrlBundle\Entity\ErrorLog;

/**
 * Keeps a database log of URL issues, such as 404 and 500 errors
 */
class Logging
{
    /**
     * Constructor
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }


    /**
     * Create a log entry for the passed request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $message
     * @return \Zicht\Bundle\UrlBundle\Entity\ErrorLog
     */
    public function createLog(Request $request, $message)
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


    /**
     * Persist the log and flush the manager.
     *
     * @param ErrorLog $entry
     * @return void
     */
    public function flush($entry)
    {
        $mgr = $this->doctrine->getManager();
        $mgr->persist($entry);
        $mgr->flush($entry);
    }
}