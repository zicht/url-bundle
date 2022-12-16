<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Event\SitemapFilterEvent;
use Zicht\Bundle\UrlBundle\Events;

/**
 * This Provider used to be optimistic, however now has been made a bit smarter while maintaining backwards compatibility.
 */
class AliasSitemapProvider implements ListableProvider
{
    /** @var Connection */
    private $connection;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(Connection $connection, EventDispatcherInterface $eventDispatcher)
    {
        $this->connection = $connection;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function all(AuthorizationCheckerInterface $authorizationChecker)
    {
        $query = $this->connection->prepare('SELECT * FROM url_alias WHERE mode=?');
        $urls = new \ArrayObject($query->executeQuery([UrlAlias::REWRITE])->fetchAllAssociative());

        /*
         * Hook to allow the mapping to be modified at run-time.
         */
        if ($this->eventDispatcher->hasListeners(Events::EVENT_SITEMAP_FILTER)) {
            $this->eventDispatcher->dispatch(new SitemapFilterEvent($urls), Events::EVENT_SITEMAP_FILTER);
        }

        return array_map(
            function ($url) {
                return ['value' => $url['public_url']];
            },
            $urls->getArrayCopy()
        );
    }
}
