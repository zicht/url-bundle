<?php
declare(strict_types=1);
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\UrlBundle\Listener;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Zicht\Bundle\UrlBundle\Entity\Site;

class MultidomainRequestListener
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $site = $this->registry->getEntityManager()->getRepository(Site::class)->findOneBy(['domain' => $request->getHttpHost()]);
        if (!$site) {
            $site = $this->registry->getEntityManager()->getRepository(Site::class)->findOneBy(['default' => true]);
        }
        $request->attributes->set('site', $site);
    }
}
