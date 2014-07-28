<?php
/**
 * @author    Philip Bergman <philip@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Robots;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Doctrine\ORM\EntityManager;

class ListBuilder
{
    /** @var array */
    protected $excludes = array();
    /** @var Router */
    protected $router;
    /** @var EntityManager */
    protected $em;
    /** @var string */
    protected $file;
    /** @var bool  */
    protected $backup = true;

    /**
     * @param Router        $router
     * @param EntityManager $em
     * @param null          $excludes
     * @param string        $file
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Router $router, EntityManager $em, $excludes = null, $file)
    {

        if (!is_file($file) || !is_writeable($file)) {
            throw new \InvalidArgumentException(sprintf('Make sure file: %s, exists and is writable by: %s', $file, shell_exec('whoami')));
        }

        $this->file   = realpath($file);
        $this->router = $router;
        $this->em     = $em;

        if (!is_null($excludes)) {
            $this->excludes = $excludes;
        }

    }

    /**
     * will build list from from url entity and from defined routes
     */
    protected function buildList()
    {
        $rules = array();

        foreach ($this->router->getRouteCollection()->all() as $name => $route) {

            $excluded = false;

            array_walk($this->excludes, function($exclude) use (&$excluded, $route){
                if (preg_match(sprintf('#^(%s)$#', strtr($exclude, array('*' => '.*', '?' => '.'))), $route->getPath())) {
                    $excluded = true;
                }
            });

            if (false === $excluded) {
                $rules[$name] = preg_replace('/{[^}]+}/', '*',$route->getPath());
            }
        }

        $urls = $this->em->getRepository('ZichtUrlBundle:UrlAlias')->findAll();

        /** @var \Zicht\Bundle\UrlBundle\Entity\UrlAlias $url */
        foreach ($urls as $url) {
            $rules[$url->getInternalUrl()] = $url->getPublicUrl();
        }

        return $rules;
    }

    /**
     * will try to write list file
     *
     * @return int
     */
    public function writeList()
    {
        if ($this->backup) {
            copy($this->file, $this->file . '~');
        }

        return file_put_contents($this->file, $this->getList());
    }

    /**
     * will return list
     *
     * @param   bool $formatted     if false will return array stack
     * @param   bool $verbose       if truw will print name after url
     *
     * @return array|string
     */
    public function getList($formatted = true, $verbose = false)
    {
        $list = $this->buildList();

        if ($formatted) {

            $date    = date('Y-m-d H:i:s');
            $return  = <<<EOF

############################################
#
# robots.txt
#
# This file is auto generated [{$date}]
#
############################################

User-agent: *

EOF;

            foreach ($list as $name => $rule) {
                $return .= sprintf("Allow:\t%s%s\n", $rule, ($verbose) ? "\t# $name" : null);
            }

            $return .= "Disallow:\t/";

        } else {
            $return = $list;
        }

        return $return;
    }

    /**
     * @return mixed
     */
    public function getExcludes()
    {
        return $this->excludes;
    }

    /**
     * @param   array $excludes
     * @return  $this;
     */
    public function setExcludes(array $excludes)
    {
        $this->excludes = $excludes;
        return $this;
    }


    /**
     * @param   string $exclude
     * @return  $this;
     */
    public function addExclude($exclude)
    {
        $this->excludes[] = $exclude;
        return $this;
    }

    /**
     * @param   boolean $backup
     * @return  $this
     */
    public function setBackup($backup)
    {
        $this->backup = $backup;
        return $this;
    }
}