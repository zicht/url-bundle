<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;

/**
 * Service that contains aliasing information
 */
class Aliasing
{
    /**
     * Overwrite an alias, if exists.
     *
     * @see addAlias
     */
    const STRATEGY_OVERWRITE    = 'overwrite';

    /**
     * Keep existing aliases and do nothing
     *
     * @see addAlias
     */
    const STRATEGY_KEEP         = 'keep';

    /**
     * Suffix existing aliases.
     *
     * @see addAlias
     */
    const STRATEGY_SUFFIX       = 'suffix';

    /**
     * @see AddAlias
     */
    const STRATEGY_IGNORE = 'ignore';

    /**
     * @see addAlias
     */
    const STRATEGY_MOVE_PREVIOUS_TO_NEW = 'redirect-previous-to-new';

    /** @var EntityManager  */
    protected $manager;

    protected $isBatch = false;

    /**
     * Initialize with doctrine
     *
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
        $this->repository = $manager->getRepository('ZichtUrlBundle:UrlAlias');
        $this->batch = array();
    }

    /**
     * Checks if the passed public url was available
     *
     * @param string $publicUrl
     * @param bool $asObject
     * @param null $mode
     * @return null
     */
    public function hasInternalAlias($publicUrl, $asObject = false, $mode = null)
    {
        $ret = null;
        if (isset($this->batch[$publicUrl])) {
            $alias = $this->batch[$publicUrl];
        } else {
            $where = array('public_url' => $publicUrl);
            if (null !== $mode) {
                $where['mode'] = $mode;
            }
            $alias = $this->getRepository()->findOneBy($where);
        }
        if ($alias) {
            $ret = ($asObject ? $alias : $alias->getInternalUrl());
        }

        return $ret;
    }


    /**
     * Check if the passed internal URL has a public url alias.
     *
     * @param string $internalUrl
     * @param bool $asObject
     * @return null
     */
    public function hasPublicAlias($internalUrl, $asObject = false)
    {
        $ret = null;

        $params = array('internal_url' => $internalUrl, 'mode' => UrlAlias::REWRITE);
        if ($alias = $this->getRepository()->findOneBy($params, array('id' => 'DESC'))) {
            $ret = ($asObject ? $alias : $alias->getPublicUrl());
        }

        return $ret;
    }

    /**
     * Find an alias matching both public and internal url
     *
     * @param string $publicUrl
     * @param string $internalUrl
     * @return null
     */
    public function findAlias($publicUrl, $internalUrl)
    {
        $ret = null;

        $params = array('public_url' => $publicUrl, 'internal_url' => $internalUrl);
        if ($alias = $this->getRepository()->findOneBy($params)) {
            $ret = $alias;
        }

        return $ret;
    }

    /**
     * Returns the repository used for storing the aliases
     *
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
    }


    /**
     * Add an alias
     *
     * When the $publicUrl already exists we will use the $conflictingPublicUrlStrategy to resolve this conflict.
     * - STRATEGY_KEEP will not do anything, i.e. the $publicUrl will keep pointing to the previous internalUrl
     * - STRATEGY_OVERWRITE will remove the previous internalUrl and replace it with $internalUrl
     * - STRATEGY_SUFFIX will modify $publicUrl by adding a '-NUMBER' suffix to make it unique
     *
     * When the $internalUrl already exists we will use the $conflictingInternalUrlStrategy to resolve this conflict.
     * - STRATEGY_IGNORE will not do anything
     * - STRATEGY_REDIRECT_PREVIOUS_TO_NEW will make make the previous publicUrl 301 to the new $publicUrl
     *
     * @param string $publicUrl
     * @param string $internalUrl
     * @param int $type
     * @param string $conflictingPublicUrlStrategy
     * @param string $conflictingInternalUrlStrategy
     * @return bool
     *
     * @throws \InvalidArgumentException
     */
    public function addAlias($publicUrl,
                             $internalUrl,
                             $type,
                             $conflictingPublicUrlStrategy = self::STRATEGY_OVERWRITE,
                             $conflictingInternalUrlStrategy = self::STRATEGY_IGNORE)
    {
        $ret = false;
        /** @var $alias UrlAlias */

        if (($alias = $this->hasPublicAlias($internalUrl, true)) && ($publicUrl !== $alias->getPublicUrl())) {
            switch ($conflictingInternalUrlStrategy) {
                case self::STRATEGY_MOVE_PREVIOUS_TO_NEW:
                    // $alias will now become the old alias, and will act as a redirect
                    $alias->setMode(UrlAlias::MOVE);
                    $this->save($alias);
                    break;
                case self::STRATEGY_IGNORE:
                    // do nothing intentionally
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid $conflictingInternalUrlStrategy');
            }
        }

        if ($alias = $this->hasInternalAlias($publicUrl, true)) {
            // when this alias is already mapped to the same internalUrl, then there is no conflict,
            // but we do need to make this alias active again
            if (($internalUrl === $alias->getInternalUrl()) && (UrlAlias::REWRITE !== $alias->getMode())) {
                $alias->setMode(UrlAlias::REWRITE);
                $this->save($alias);
                $ret = true;
            } else {

                switch ($conflictingPublicUrlStrategy) {
                    case self::STRATEGY_OVERWRITE:
                        $alias->setInternalUrl($internalUrl);
                        $this->save($alias);
                        $ret = true;
                        break;
                    case self::STRATEGY_KEEP:
                        // do nothing intentionally
                        break;
                    case self::STRATEGY_SUFFIX:
                        $original = $publicUrl;
                        $i = 1;
                        do {
                            $publicUrl = $original . '-' . ($i++);
                        } while ($this->hasInternalAlias($publicUrl));

                        $alias = new UrlAlias($publicUrl, $internalUrl, $type);
                        $this->save($alias);
                        $ret = true;
                        break;
                    default:
                        throw new \InvalidArgumentException('Invalid $conflictingPublicUrlStrategy');
                }
            }
        } else {
            $alias = new UrlAlias($publicUrl, $internalUrl, $type);
            $this->save($alias);
            $ret = true;
        }
        return $ret;
    }

    /**
     * Changes the $publicUrl of an UrlAlias to $newPublicUrl
     * Adds a new UrlAlias with $type and point it to $newPublicUrl
     *
     * Given an existing UrlAlias pointing:
     *      A -> B
     * After this method has been run
     *      A -> C and C -> B
     * Where A -> C is a new UrlAlias of the type $type
     * Where C -> B is an existing UrlAlias with the publicUrl changed
     *
     * @param string $newPublicUrl The new public url to move to the alias to.
     * @param string $publicUrl    The current public url of the UrlAlias we're moving.
     * @param string $internalUrl  The current internal url of the UrlAlias we're moving
     * @param integer $type        The type of move we want to make. a.k.a. "mode"
     * @return boolean Wheter the move action was successful.
     */
    public function moveAlias($newPublicUrl, $publicUrl, $internalUrl, $type = UrlAlias::ALIAS)
    {
        $moved = false;
        if ($newPublicUrl === $publicUrl) {
            return $moved;
        }
        /** @var UrlAlias $existingAlias */
        $existingAlias = $this->findAlias($publicUrl, $internalUrl);
        $newAliasExists = $this->hasInternalAlias($newPublicUrl, true);
        // if the old alias exists, and the new one doesn't
        if (!is_null($existingAlias) && is_null($newAliasExists)) {

            // change the old alias
            $existingAlias->setPublicUrl($newPublicUrl);
            // create a new one
            $newAlias = new UrlAlias();
            $newAlias->setPublicUrl($publicUrl);
            $newAlias->setInternalUrl($newPublicUrl);
            $newAlias->setMode($type);

            $this->save($existingAlias);
            $this->save($newAlias);
            $moved = true;
        }
        return $moved;
    }


    /**
     * Set the batch to 'true' if aliases are being batch processed (optimization).
     *
     * This method returns a callback that needs to be executed after the batch is done; this is up to the caller.
     *
     * @param bool $isBatch
     * @return callable
     */
    public function setIsBatch($isBatch)
    {
        $this->batch = array();
        $this->isBatch = $isBatch;
        $mgr = $this->manager;
        $self = $this;
        return function() use($mgr, $self) {
            $mgr->flush();
            $self->setIsBatch(true);
        };
    }


    /**
     * Persist the URL alias.
     *
     * @param \Zicht\Bundle\UrlBundle\Entity\UrlAlias $alias
     * @return void
     */
    protected function save(UrlAlias $alias)
    {
        $this->manager->persist($alias);

        if ($this->isBatch) {
            $this->batch[$alias->getPublicUrl()]= $alias;
        } else {
            $this->manager->flush($alias);
        }
    }


    /**
     * Compact redirects; i.e. optimize redirects:
     *
     * If /a points to /b, and /b points to /c, let /a point to /c
     *
     * @return void
     */
    public function compact()
    {
        foreach ($this->getRepository()->findAll() as $urlAlias) {
            if ($cascadingAlias = $this->hasPublicAlias($urlAlias->internal_url)) {
                $urlAlias->setInternalUrl($cascadingAlias->getInternalUrl());
            }
        }
    }


    /**
     * Remove alias
     *
     * @param string $internalUrl
     * @return void
     */
    public function removeAlias($internalUrl)
    {
        if ($alias = $this->hasPublicAlias($internalUrl, true)) {
            $this->manager->remove($alias);
            $this->manager->flush($alias);
        }
    }

    /**
     * Takes HTML text and replaces all <a href='INTERNAL'> into <a href='PUBLIC'>.
     *
     * @param string $html
     * @param Request $request
     * @return string
     */
    public function internalToPublicHtml($html, $request)
    {
        return HtmlMapper::processAliasingInHtml($html, 'internal-to-public', $this, $request);
    }

    /**
     * Takes HTML text and replaces all <a href='PUBLIC'> into <a href='INTERNAL'>.
     *
     * @param string $html
     * @return string
     */
    public function publicToInternalHtml($html)
    {
        return HtmlMapper::processAliasingInHtml($html, 'public-to-internal', $this);
    }


    /**
     * Returns key/value pairs of a list of url's.
     *
     * @param string[] $urls
     * @param string $mode
     * @return array
     */
    public function getAliasingMap($urls, $mode)
    {
        switch ($mode) {
            case 'internal-to-public':
                $from = 'internal_url';
                $to = 'public_url';
                break;
            case 'public-to-internal':
                $from = 'public_url';
                $to = 'internal_url';
                break;
            default:
                throw new \InvalidArgumentException("Invalid mode supplied: {$mode}");
        }

        $connection = $this->manager->getConnection()->getWrappedConnection();

        $sql = sprintf(
            'SELECT %1$s, %2$s FROM url_alias WHERE mode=%3$d AND %1$s IN(%4$s)',
            $from,
            $to,
            UrlAlias::REWRITE,
            join(
                ', ',
                array_map(
                    function($v) use($connection) {
                        return $connection->quote($v, \PDO::PARAM_STR);
                    },
                    $urls
                )
            )
        );

        if ($stmt = $connection->query($sql)) {
            return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
        }
        return array();
    }
}
