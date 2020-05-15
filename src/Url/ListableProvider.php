<?php
/**
 * @copyright Zicht online <http://zicht.nl>
 */
namespace Zicht\Bundle\UrlBundle\Url;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Implementing a listable provider will make the url's available on the
 * URL suggest usable by TinyMCE. See the SuggestUrlController
 */
interface ListableProvider
{
    /**
     * List all URL's
     *
     * The securitycontext must be passed so it is available for the provider to check access rights.
     *
     * @param AuthorizationCheckerInterface $securityContextInterface
     * @return array
     */
    public function all(AuthorizationCheckerInterface $securityContextInterface);
}
