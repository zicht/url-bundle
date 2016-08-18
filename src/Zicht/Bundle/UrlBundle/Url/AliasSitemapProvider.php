<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Url;

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Zicht\Bundle\UrlBundle\Entity\UrlAlias;
use Zicht\Bundle\UrlBundle\Url\ListableProvider;

/**
 * This optimistic implementation assumes that all values in `public_url` are reachable for an anonymous user, i.e.
 * "public", and can be included in the sitemap
 */
class AliasSitemapProvider implements ListableProvider
{
    /**
     * Constructor
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    /**
     * @{inheritDoc}
     */
    public function all(AuthorizationCheckerInterface $securityContextInterface)
    {
        $q = $this->connection->prepare('SELECT public_url FROM url_alias WHERE mode=?');
        $q->execute([UrlAlias::REWRITE]);

        return array_map(
            function ($url) {
                return ['value' => $url];
            },
            $q->fetchAll(\PDO::FETCH_COLUMN)
        );
    }
}
