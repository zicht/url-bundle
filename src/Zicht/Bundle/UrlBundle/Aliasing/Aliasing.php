<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Aliasing;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Zicht\Bundle\UrlBundle\Aliasing\Mapper\UrlMapperInterface;
use Zicht\Bundle\UrlBundle\Entity\Repository\UrlAliasRepository;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Url\Rewriter;

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

    /** @var UrlAliasRepository */
    protected $repository;

    protected $isBatch = false;


    /**
     * Mappers that, based on the content type, can transform internal urls to public urls
     *
     * @var UrlMapperInterface[]
     */
    private $contentMappers = array();

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
     * Assert if the strategy is ok when the public url already exists.
     *
     * @param string $conflictingPublicUrlStrategy
     * @return void
     */
    public static function validatePublicConflictingStrategy($conflictingPublicUrlStrategy)
    {
        if (!in_array($conflictingPublicUrlStrategy, [self::STRATEGY_KEEP, self::STRATEGY_OVERWRITE, self::STRATEGY_SUFFIX])) {
            throw new \InvalidArgumentException("Invalid \$conflictingPublicUrlStrategy '$conflictingPublicUrlStrategy'");
        }
    }

    /**
     * Assert if the strategy is ok when the internal url already has a public url.
     *
     * @param string $conflictingInternalUrlStrategy
     * @return void
     */
    public static function validateInternalConflictingStrategy($conflictingInternalUrlStrategy)
    {
        if (!in_array($conflictingInternalUrlStrategy, [self::STRATEGY_IGNORE, self::STRATEGY_MOVE_PREVIOUS_TO_NEW])) {
            throw new \InvalidArgumentException("Invalid \$conflictingInternalUrlStrategy '$conflictingInternalUrlStrategy'");
        }
    }

    /**
     * Checks if the passed public url is currently mapped to an internal url
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
            $alias = $this->repository->findOneByPublicUrl($publicUrl, $mode);
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

        if ($alias = $this->repository->findOneByInternalUrl($internalUrl)) {
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
     * @return UrlAliasRepository
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
     * - STRATEGY_MOVE_PREVIOUS_TO_NEW will make make the previous publicUrl 301 to the new $publicUrl
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
    public function addAlias(
        $publicUrl,
        $internalUrl,
        $type,
        $conflictingPublicUrlStrategy = self::STRATEGY_OVERWRITE,
        $conflictingInternalUrlStrategy = self::STRATEGY_IGNORE
    ) {
        self::validateInternalConflictingStrategy($conflictingInternalUrlStrategy);
        self::validatePublicConflictingStrategy($conflictingPublicUrlStrategy);

        $ret = false;
        /** @var $alias UrlAlias */

        // if the internal url is currently already aliased
        $alias = $this->hasPublicAlias($internalUrl, true);

        if ($alias) {
            switch ($conflictingInternalUrlStrategy) {
                case self::STRATEGY_MOVE_PREVIOUS_TO_NEW:
                    if ($alias && ($publicUrl !== $alias->getPublicUrl())) {
                        // $alias will now become the old alias, and will act as a redirect
                        $alias->setMode(UrlAlias::MOVE);
                        $this->save($alias);
                    }
                    break;
                case self::STRATEGY_IGNORE:
                    // Alias already exist, but the strategy is to ignore changes
                    return $ret;
                default:
                    // case is handled in the 'if' guard at top of the function
                    break;
            }
        }

        if ($alias = $this->hasInternalAlias($publicUrl, true)) {

            // when this alias is already mapped to the same internalUrl, then there is no conflict,
            // but we do need to make this alias active again
            if ($internalUrl === $alias->getInternalUrl()) {
                if (UrlAlias::REWRITE === $alias->getMode()) {
                    // no need to do anything
                    $ret = true;
                } else {
                    // we can reuse an existing alias.  The page will get exactly the url it wants
                    $alias->setMode(UrlAlias::REWRITE);
                    $this->save($alias);
                    $ret = true;
                }
            }

            // it is also possible to use one of the pre-existing aliases (that were created using the STRATEGY_SUFFIX)
            if (!$ret) {
                foreach ($this->getRepository()->findAllByInternalUrl($internalUrl) as $alternate) {
                    if (UrlAlias::REWRITE !== $alternate->getMode()) {
                        if (preg_match(sprintf('#^%s-[0-9]+$#', preg_quote($publicUrl)), $alternate->getPublicUrl(), $match)) {
                            // we can reuse an existing alias.  The page will get a suffixed version of the url it wants
                            $alternate->setMode(UrlAlias::REWRITE);
                            $this->save($alternate);
                            $ret = true;
                            break;
                        }
                    }
                }
            }

            // otherwise we will need to solve the conflict using the supplied strategy
            if (!$ret) {
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
                        // case is handled in the 'if' guard at top of the function
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
        return function () use ($mgr, $self) {
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
     * Returns key/value pairs of a list of url's.
     *
     * @param string[] $urls
     * @param string $mode
     * @return array
     */
    public function getAliasingMap($urls, $mode)
    {
        if (0 === count($urls)) {
            return [];
        }

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
                    function ($v) use ($connection) {
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

    /**
     * Transform internal URLS to public URLS using our defined mappers
     *
     * @param string $contentType
     * @param string $mode
     * @param string $content
     * @param string[] $hosts
     * @return string
     */
    public function mapContent($contentType, $mode, $content, $hosts)
    {
        $rewriter = new Rewriter($this);
        $rewriter->setLocalDomains($hosts);

        foreach ($this->contentMappers as $mapper) {
            if ($mapper->supports($contentType)) {
                return $mapper->processAliasing($content, $mode, $rewriter);
            }
        }

        return $content;
    }

    /**
     * Add a new content mapper to our aliasing class
     *
     * @param UrlMapperInterface $mapper
     *
     * @return void
     */
    public function addMapper(UrlMapperInterface $mapper)
    {
        $this->contentMappers[] = $mapper;
    }
}
