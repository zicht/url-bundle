<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Event\SitemapFilterEvent;
use Zicht\Bundle\UrlBundle\Event\SitemapQueryEvent;
use Zicht\Bundle\UrlBundle\Events;
use Zicht\Bundle\UrlBundle\Url\ListableProvider;
use function Zicht\Itertools\iterable;

/**
 * This Provider used to be optimistic, however now has been made a bit smarter while maintaining backwards compatibility.
 */
class AliasSitemapProvider implements ListableProvider
{
    /**
     * Constructor
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection, EventDispatcherInterface $eventDispatcher)
    {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @{inheritDoc}
     */
    public function all(AuthorizationCheckerInterface $authorizationChecker): array
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('*')
            ->from('url_alias')
            ->where('mode = :mode');

        $queryBuilder->setParameter('mode', UrlAlias::REWRITE);

        /**
         * Hook to allow the query to be modified at run-time.
         */
        if ($this->eventDispatcher->hasListeners(Events::EVENT_SITEMAP_QUERY)) {
            $event = $this->eventDispatcher->dispatch(
                Events::EVENT_SITEMAP_QUERY,
                new SitemapQueryEvent(
                    $queryBuilder, [
                        'authorization_checker' => $authorizationChecker,
                    ]
                )
            );

            $queryBuilder = $event->getSubject();
        }

        $urls = iterable($queryBuilder->execute()->fetchAll(\PDO::FETCH_ASSOC));

        /**
         * Hook to allow the mapping to be modified at run-time.
         */
        if ($this->eventDispatcher->hasListeners(Events::EVENT_SITEMAP_FILTER)) {
            $event = $this->eventDispatcher->dispatch(
                Events::EVENT_SITEMAP_FILTER,
                new SitemapFilterEvent(
                    $urls, [
                        'authorization_checker' => $authorizationChecker,
                        'connection' => $this->connection,
                    ]
                )
            );

            $urls = $event->getSubject();
        }

        return array_map(
            function ($url) {
                return ['value' => $url['public_url']];
            },
            $urls->toArray()
        );
    }
}
